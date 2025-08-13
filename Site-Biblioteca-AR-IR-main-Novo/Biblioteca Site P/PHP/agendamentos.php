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
        criarAgendamento();
        break;
    case 'listar':
        listarAgendamentos();
        break;
    case 'listar_usuario':
        listarAgendamentosUsuario();
        break;
    case 'confirmar':
        confirmarAgendamento();
        break;
    case 'cancelar':
        cancelarAgendamento();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function criarAgendamento() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $livro_id = intval($_POST['livro_id'] ?? 0);
    $data_retirada = $_POST['data_retirada'] ?? '';
    $observacoes = limparDados($_POST['observacoes'] ?? '');
    
    if ($livro_id <= 0) {
        respostaJson(false, 'Livro inválido');
    }
    
    if (empty($data_retirada)) {
        respostaJson(false, 'Data de retirada é obrigatória');
    }
    
    // Validar data de retirada (deve ser futura)
    $data_retirada_obj = new DateTime($data_retirada);
    $data_atual = new DateTime();
    
    if ($data_retirada_obj <= $data_atual) {
        respostaJson(false, 'Data de retirada deve ser futura');
    }
    
    // Limitar agendamento para até 30 dias no futuro
    $data_limite = clone $data_atual;
    $data_limite->add(new DateInterval('P30D'));
    
    if ($data_retirada_obj > $data_limite) {
        respostaJson(false, 'Agendamento pode ser feito no máximo 30 dias no futuro');
    }
    
    try {
        // Verificar se o livro existe e está ativo
        $stmt = $pdo->prepare("SELECT id, titulo FROM livros WHERE id = ? AND ativo = 1");
        $stmt->execute([$livro_id]);
        $livro = $stmt->fetch();
        
        if (!$livro) {
            respostaJson(false, 'Livro não encontrado');
        }
        
        // Verificar se o usuário já tem agendamento ativo para este livro
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM agendamentos 
            WHERE usuario_id = ? AND livro_id = ? AND status IN ('pendente', 'confirmado')
        ");
        $stmt->execute([$usuario_id, $livro_id]);
        $agendamento_existente = $stmt->fetch();
        
        if ($agendamento_existente['total'] > 0) {
            respostaJson(false, 'Você já possui um agendamento ativo para este livro');
        }
        
        // Verificar se o usuário tem empréstimo ativo deste livro
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM emprestimos 
            WHERE usuario_id = ? AND livro_id = ? AND status IN ('ativo', 'atrasado')
        ");
        $stmt->execute([$usuario_id, $livro_id]);
        $emprestimo_existente = $stmt->fetch();
        
        if ($emprestimo_existente['total'] > 0) {
            respostaJson(false, 'Você já possui este livro emprestado');
        }
        
        // Criar agendamento
        $stmt = $pdo->prepare("
            INSERT INTO agendamentos (usuario_id, livro_id, data_retirada_prevista, observacoes) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$usuario_id, $livro_id, $data_retirada, $observacoes]);
        
        respostaJson(true, 'Agendamento criado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao criar agendamento: ' . $e->getMessage());
    }
}

function listarAgendamentos() {
    global $pdo;
    
    verificarAdmin();
    
    try {
        $stmt = $pdo->query("
            SELECT a.*, u.nome as usuario_nome, l.titulo as livro_titulo, l.autor as livro_autor
            FROM agendamentos a
            JOIN usuarios u ON a.usuario_id = u.id
            JOIN livros l ON a.livro_id = l.id
            ORDER BY a.data_agendamento DESC
        ");
        $agendamentos = $stmt->fetchAll();
        
        respostaJson(true, 'Agendamentos listados com sucesso', $agendamentos);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar agendamentos: ' . $e->getMessage());
    }
}

function listarAgendamentosUsuario() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, l.titulo as livro_titulo, l.autor as livro_autor
            FROM agendamentos a
            JOIN livros l ON a.livro_id = l.id
            WHERE a.usuario_id = ?
            ORDER BY a.data_agendamento DESC
        ");
        $stmt->execute([$usuario_id]);
        $agendamentos = $stmt->fetchAll();
        
        respostaJson(true, 'Agendamentos listados com sucesso', $agendamentos);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar agendamentos: ' . $e->getMessage());
    }
}

function confirmarAgendamento() {
    global $pdo;
    
    verificarAdmin();
    
    $agendamento_id = intval($_POST['agendamento_id'] ?? 0);
    
    if ($agendamento_id <= 0) {
        respostaJson(false, 'ID do agendamento inválido');
    }
    
    try {
        // Verificar se o agendamento existe e está pendente
        $stmt = $pdo->prepare("
            SELECT a.*, l.quantidade_disponivel 
            FROM agendamentos a
            JOIN livros l ON a.livro_id = l.id
            WHERE a.id = ? AND a.status = 'pendente'
        ");
        $stmt->execute([$agendamento_id]);
        $agendamento = $stmt->fetch();
        
        if (!$agendamento) {
            respostaJson(false, 'Agendamento não encontrado ou já processado');
        }
        
        // Verificar se o livro está disponível
        if ($agendamento['quantidade_disponivel'] <= 0) {
            respostaJson(false, 'Livro não está disponível para retirada');
        }
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Confirmar agendamento
        $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'confirmado' WHERE id = ?");
        $stmt->execute([$agendamento_id]);
        
        // Reduzir quantidade disponível do livro
        $stmt = $pdo->prepare("UPDATE livros SET quantidade_disponivel = quantidade_disponivel - 1 WHERE id = ?");
        $stmt->execute([$agendamento['livro_id']]);
        
        $pdo->commit();
        
        respostaJson(true, 'Agendamento confirmado com sucesso');
    } catch (PDOException $e) {
        $pdo->rollBack();
        respostaJson(false, 'Erro ao confirmar agendamento: ' . $e->getMessage());
    }
}

function cancelarAgendamento() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $agendamento_id = intval($_POST['agendamento_id'] ?? 0);
    
    if ($agendamento_id <= 0) {
        respostaJson(false, 'ID do agendamento inválido');
    }
    
    try {
        // Verificar se o agendamento existe e pertence ao usuário
        $stmt = $pdo->prepare("
            SELECT a.*, l.id as livro_id 
            FROM agendamentos a
            JOIN livros l ON a.livro_id = l.id
            WHERE a.id = ? AND a.usuario_id = ? AND a.status IN ('pendente', 'confirmado')
        ");
        $stmt->execute([$agendamento_id, $usuario_id]);
        $agendamento = $stmt->fetch();
        
        if (!$agendamento) {
            respostaJson(false, 'Agendamento não encontrado ou não pode ser cancelado');
        }
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Cancelar agendamento
        $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ?");
        $stmt->execute([$agendamento_id]);
        
        // Se estava confirmado, aumentar quantidade disponível do livro
        if ($agendamento['status'] === 'confirmado') {
            $stmt = $pdo->prepare("UPDATE livros SET quantidade_disponivel = quantidade_disponivel + 1 WHERE id = ?");
            $stmt->execute([$agendamento['livro_id']]);
        }
        
        $pdo->commit();
        
        respostaJson(true, 'Agendamento cancelado com sucesso');
    } catch (PDOException $e) {
        $pdo->rollBack();
        respostaJson(false, 'Erro ao cancelar agendamento: ' . $e->getMessage());
    }
}
?>
