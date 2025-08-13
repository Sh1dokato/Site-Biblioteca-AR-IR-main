<?php
require_once 'config.php';

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'criar':
        criarEmprestimo();
        break;
    case 'listar':
        listarEmprestimos();
        break;
    case 'listar_usuario':
        listarEmprestimosUsuario();
        break;
    case 'devolver':
        devolverLivro();
        break;
    case 'pagar_multa':
        pagarMulta();
        break;
    case 'calcular_multa':
        calcularMulta();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function criarEmprestimo() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $livro_id = intval($_POST['livro_id'] ?? 0);
    $dias_emprestimo = intval($_POST['dias'] ?? 15);
    
    if ($livro_id <= 0) {
        respostaJson(false, 'Livro inválido');
    }
    
    if ($dias_emprestimo < 1 || $dias_emprestimo > 30) {
        respostaJson(false, 'Período de empréstimo deve ser entre 1 e 30 dias');
    }
    
    try {
        // Verificar se o livro está disponível
        $stmt = $pdo->prepare("SELECT quantidade_disponivel FROM livros WHERE id = ? AND ativo = 1");
        $stmt->execute([$livro_id]);
        $livro = $stmt->fetch();
        
        if (!$livro) {
            respostaJson(false, 'Livro não encontrado');
        }
        
        if ($livro['quantidade_disponivel'] <= 0) {
            respostaJson(false, 'Livro não está disponível para empréstimo');
        }
        
        // Verificar se o usuário já tem este livro emprestado
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emprestimos WHERE usuario_id = ? AND livro_id = ? AND status IN ('ativo', 'atrasado')");
        $stmt->execute([$usuario_id, $livro_id]);
        $emprestimo_existente = $stmt->fetch();
        
        if ($emprestimo_existente['total'] > 0) {
            respostaJson(false, 'Você já possui este livro emprestado');
        }
        
        // Verificar se o usuário tem multas pendentes
        $stmt = $pdo->prepare("SELECT SUM(multa) as total_multa FROM emprestimos WHERE usuario_id = ? AND multa_paga = 0 AND status IN ('devolvido', 'atrasado')");
        $stmt->execute([$usuario_id]);
        $multas = $stmt->fetch();
        
        if ($multas['total_multa'] > 0) {
            respostaJson(false, 'Você possui multas pendentes. Pague-as antes de fazer novos empréstimos.');
        }
        
        // Calcular data de devolução
        $data_devolucao = date('Y-m-d', strtotime("+$dias_emprestimo days"));
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Criar empréstimo
        $stmt = $pdo->prepare("INSERT INTO emprestimos (usuario_id, livro_id, data_devolucao_prevista) VALUES (?, ?, ?)");
        $stmt->execute([$usuario_id, $livro_id, $data_devolucao]);
        
        // Atualizar quantidade disponível
        $stmt = $pdo->prepare("UPDATE livros SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = ?");
        $stmt->execute([$livro_id]);
        
        $pdo->commit();
        
        respostaJson(true, 'Empréstimo realizado com sucesso');
    } catch (PDOException $e) {
        $pdo->rollBack();
        respostaJson(false, 'Erro ao criar empréstimo: ' . $e->getMessage());
    }
}

function listarEmprestimos() {
    global $pdo;
    
    verificarAdmin();
    
    try {
        $stmt = $pdo->query("
            SELECT e.*, u.nome as usuario_nome, l.titulo as livro_titulo, l.autor as livro_autor
            FROM emprestimos e
            JOIN usuarios u ON e.usuario_id = u.id
            JOIN livros l ON e.livro_id = l.id
            ORDER BY e.data_emprestimo DESC
        ");
        $emprestimos = $stmt->fetchAll();
        
        respostaJson(true, 'Empréstimos listados com sucesso', $emprestimos);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar empréstimos: ' . $e->getMessage());
    }
}

function listarEmprestimosUsuario() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, l.titulo as livro_titulo, l.autor as livro_autor
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            WHERE e.usuario_id = ?
            ORDER BY e.data_emprestimo DESC
        ");
        $stmt->execute([$usuario_id]);
        $emprestimos = $stmt->fetchAll();
        
        respostaJson(true, 'Empréstimos listados com sucesso', $emprestimos);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar empréstimos: ' . $e->getMessage());
    }
}

function devolverLivro() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $emprestimo_id = intval($_POST['emprestimo_id'] ?? 0);
    
    if ($emprestimo_id <= 0) {
        respostaJson(false, 'ID do empréstimo inválido');
    }
    
    try {
        // Verificar se o empréstimo existe e pertence ao usuário
        $stmt = $pdo->prepare("
            SELECT e.*, l.id as livro_id 
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            WHERE e.id = ? AND e.usuario_id = ? AND e.status IN ('ativo', 'atrasado')
        ");
        $stmt->execute([$emprestimo_id, $usuario_id]);
        $emprestimo = $stmt->fetch();
        
        if (!$emprestimo) {
            respostaJson(false, 'Empréstimo não encontrado ou já devolvido');
        }
        
        // Calcular multa se houver atraso
        $data_atual = new DateTime();
        $data_devolucao = new DateTime($emprestimo['data_devolucao_prevista']);
        $multa = 0;
        
        if ($data_atual > $data_devolucao) {
            $dias_atraso = $data_atual->diff($data_devolucao)->days;
            $multa = $dias_atraso * 0.50; // R$ 0,50 por dia de atraso
        }
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Atualizar empréstimo
        $stmt = $pdo->prepare("
            UPDATE emprestimos 
            SET status = 'devolvido', 
                data_devolucao_real = NOW(), 
                multa = ?
            WHERE id = ?
        ");
        $stmt->execute([$multa, $emprestimo_id]);
        
        // Atualizar quantidade disponível do livro
        $stmt = $pdo->prepare("UPDATE livros SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = ?");
        $stmt->execute([$emprestimo['livro_id']]);
        
        $pdo->commit();
        
        $mensagem = 'Livro devolvido com sucesso';
        if ($multa > 0) {
            $mensagem .= ". Multa de R$ " . number_format($multa, 2, ',', '.') . " aplicada por atraso.";
        }
        
        respostaJson(true, $mensagem, ['multa' => $multa]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        respostaJson(false, 'Erro ao devolver livro: ' . $e->getMessage());
    }
}

function pagarMulta() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $emprestimo_id = intval($_POST['emprestimo_id'] ?? 0);
    $metodo_pagamento = $_POST['metodo'] ?? '';
    
    if ($emprestimo_id <= 0) {
        respostaJson(false, 'ID do empréstimo inválido');
    }
    
    if (!in_array($metodo_pagamento, ['pix', 'boleto', 'cartao', 'doacao'])) {
        respostaJson(false, 'Método de pagamento inválido');
    }
    
    try {
        // Verificar se o empréstimo existe e tem multa
        $stmt = $pdo->prepare("
            SELECT multa, multa_paga 
            FROM emprestimos 
            WHERE id = ? AND usuario_id = ? AND status = 'devolvido'
        ");
        $stmt->execute([$emprestimo_id, $usuario_id]);
        $emprestimo = $stmt->fetch();
        
        if (!$emprestimo) {
            respostaJson(false, 'Empréstimo não encontrado');
        }
        
        if ($emprestimo['multa_paga']) {
            respostaJson(false, 'Multa já foi paga');
        }
        
        if ($emprestimo['multa'] <= 0) {
            respostaJson(false, 'Não há multa para pagar');
        }
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Registrar pagamento
        $stmt = $pdo->prepare("
            INSERT INTO pagamentos_multa (emprestimo_id, valor, metodo_pagamento, status) 
            VALUES (?, ?, ?, 'confirmado')
        ");
        $stmt->execute([$emprestimo_id, $emprestimo['multa'], $metodo_pagamento]);
        
        // Marcar multa como paga
        $stmt = $pdo->prepare("UPDATE emprestimos SET multa_paga = 1 WHERE id = ?");
        $stmt->execute([$emprestimo_id]);
        
        $pdo->commit();
        
        respostaJson(true, 'Multa paga com sucesso');
    } catch (PDOException $e) {
        $pdo->rollBack();
        respostaJson(false, 'Erro ao pagar multa: ' . $e->getMessage());
    }
}

function calcularMulta() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $emprestimo_id = intval($_POST['emprestimo_id'] ?? 0);
    
    if ($emprestimo_id <= 0) {
        respostaJson(false, 'ID do empréstimo inválido');
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT multa, multa_paga, data_devolucao_prevista, status
            FROM emprestimos 
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->execute([$emprestimo_id, $usuario_id]);
        $emprestimo = $stmt->fetch();
        
        if (!$emprestimo) {
            respostaJson(false, 'Empréstimo não encontrado');
        }
        
        $multa = $emprestimo['multa'];
        
        // Se ainda não foi devolvido, calcular multa potencial
        if ($emprestimo['status'] === 'ativo' || $emprestimo['status'] === 'atrasado') {
            $data_atual = new DateTime();
            $data_devolucao = new DateTime($emprestimo['data_devolucao_prevista']);
            
            if ($data_atual > $data_devolucao) {
                $dias_atraso = $data_atual->diff($data_devolucao)->days;
                $multa = $dias_atraso * 0.50;
            }
        }
        
        respostaJson(true, 'Multa calculada com sucesso', [
            'multa' => $multa,
            'multa_paga' => $emprestimo['multa_paga']
        ]);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao calcular multa: ' . $e->getMessage());
    }
}
?>
