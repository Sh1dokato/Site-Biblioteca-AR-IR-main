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
        criarDoacao();
        break;
    case 'listar':
        listarDoacoes();
        break;
    case 'listar_usuario':
        listarDoacoesUsuario();
        break;
    case 'aprovar':
        aprovarDoacao();
        break;
    case 'rejeitar':
        rejeitarDoacao();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function criarDoacao() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $tipo_doacao = $_POST['tipo_doacao'] ?? '';
    $descricao = limparDados($_POST['descricao'] ?? '');
    $valor = floatval($_POST['valor'] ?? 0);
    
    if (!in_array($tipo_doacao, ['livro', 'item_higiene', 'dinheiro'])) {
        respostaJson(false, 'Tipo de doação inválido');
    }
    
    if (empty($descricao)) {
        respostaJson(false, 'Descrição é obrigatória');
    }
    
    if ($tipo_doacao === 'dinheiro' && $valor <= 0) {
        respostaJson(false, 'Valor deve ser maior que zero para doações em dinheiro');
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO doacoes (usuario_id, tipo_doacao, descricao, valor) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$usuario_id, $tipo_doacao, $descricao, $valor]);
        
        respostaJson(true, 'Doação registrada com sucesso. Aguardando aprovação do administrador.');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao registrar doação: ' . $e->getMessage());
    }
}

function listarDoacoes() {
    global $pdo;
    
    verificarAdmin();
    
    try {
        $stmt = $pdo->query("
            SELECT d.*, u.nome as usuario_nome
            FROM doacoes d
            JOIN usuarios u ON d.usuario_id = u.id
            ORDER BY d.data_doacao DESC
        ");
        $doacoes = $stmt->fetchAll();
        
        respostaJson(true, 'Doações listadas com sucesso', $doacoes);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar doações: ' . $e->getMessage());
    }
}

function listarDoacoesUsuario() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM doacoes 
            WHERE usuario_id = ?
            ORDER BY data_doacao DESC
        ");
        $stmt->execute([$usuario_id]);
        $doacoes = $stmt->fetchAll();
        
        respostaJson(true, 'Doações listadas com sucesso', $doacoes);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar doações: ' . $e->getMessage());
    }
}

function aprovarDoacao() {
    global $pdo;
    
    verificarAdmin();
    
    $doacao_id = intval($_POST['doacao_id'] ?? 0);
    $admin_id = $_SESSION['usuario_id'];
    
    if ($doacao_id <= 0) {
        respostaJson(false, 'ID da doação inválido');
    }
    
    try {
        // Verificar se a doação existe e está pendente
        $stmt = $pdo->prepare("
            SELECT * FROM doacoes 
            WHERE id = ? AND status = 'pendente'
        ");
        $stmt->execute([$doacao_id]);
        $doacao = $stmt->fetch();
        
        if (!$doacao) {
            respostaJson(false, 'Doação não encontrada ou já processada');
        }
        
        // Aprovar doação
        $stmt = $pdo->prepare("
            UPDATE doacoes 
            SET status = 'aprovada', 
                data_aprovacao = NOW(), 
                admin_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$admin_id, $doacao_id]);
        
        // Se for doação de livro, adicionar ao acervo
        if ($doacao['tipo_doacao'] === 'livro') {
            // Extrair informações do livro da descrição (formato simples)
            $descricao = $doacao['descricao'];
            
            // Tentar extrair título e autor da descrição
            $partes = explode(' - ', $descricao);
            $titulo = trim($partes[0] ?? 'Livro doado');
            $autor = trim($partes[1] ?? 'Autor não informado');
            
            $stmt = $pdo->prepare("
                INSERT INTO livros (titulo, autor, categoria, quantidade_total, quantidade_disponivel) 
                VALUES (?, ?, 'Doação', 1, 1)
            ");
            $stmt->execute([$titulo, $autor]);
        }
        
        respostaJson(true, 'Doação aprovada com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao aprovar doação: ' . $e->getMessage());
    }
}

function rejeitarDoacao() {
    global $pdo;
    
    verificarAdmin();
    
    $doacao_id = intval($_POST['doacao_id'] ?? 0);
    $motivo = limparDados($_POST['motivo'] ?? '');
    $admin_id = $_SESSION['usuario_id'];
    
    if ($doacao_id <= 0) {
        respostaJson(false, 'ID da doação inválido');
    }
    
    try {
        // Verificar se a doação existe e está pendente
        $stmt = $pdo->prepare("
            SELECT * FROM doacoes 
            WHERE id = ? AND status = 'pendente'
        ");
        $stmt->execute([$doacao_id]);
        $doacao = $stmt->fetch();
        
        if (!$doacao) {
            respostaJson(false, 'Doação não encontrada ou já processada');
        }
        
        // Rejeitar doação
        $observacoes = !empty($motivo) ? "Motivo da rejeição: $motivo" : "Doação rejeitada pelo administrador";
        
        $stmt = $pdo->prepare("
            UPDATE doacoes 
            SET status = 'rejeitada', 
                data_aprovacao = NOW(), 
                admin_id = ?,
                observacoes = ?
            WHERE id = ?
        ");
        $stmt->execute([$admin_id, $observacoes, $doacao_id]);
        
        respostaJson(true, 'Doação rejeitada com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao rejeitar doação: ' . $e->getMessage());
    }
}
?>
