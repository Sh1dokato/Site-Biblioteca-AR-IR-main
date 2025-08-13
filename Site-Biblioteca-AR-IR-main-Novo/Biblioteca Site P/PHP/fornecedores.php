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
    case 'listar':
        listarFornecedores();
        break;
    case 'adicionar':
        adicionarFornecedor();
        break;
    case 'editar':
        editarFornecedor();
        break;
    case 'excluir':
        excluirFornecedor();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function listarFornecedores() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome");
        $fornecedores = $stmt->fetchAll();
        
        respostaJson(true, 'Fornecedores listados com sucesso', $fornecedores);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar fornecedores: ' . $e->getMessage());
    }
}

function adicionarFornecedor() {
    global $pdo;
    
    $nome = limparDados($_POST['nome'] ?? '');
    $cnpj = limparDados($_POST['cnpj'] ?? '');
    $telefone = limparDados($_POST['telefone'] ?? '');
    $email = limparDados($_POST['email'] ?? '');
    $endereco = limparDados($_POST['endereco'] ?? '');
    
    if (empty($nome) || empty($cnpj) || empty($telefone)) {
        respostaJson(false, 'Nome, CNPJ e telefone são obrigatórios');
    }
    
    // Validar CNPJ (apenas verificar se tem 14 dígitos)
    if (strlen($cnpj) !== 14 || !is_numeric($cnpj)) {
        respostaJson(false, 'CNPJ deve conter 14 dígitos numéricos');
    }
    
    // Validar telefone
    if (strlen($telefone) < 10 || strlen($telefone) > 11 || !is_numeric($telefone)) {
        respostaJson(false, 'Telefone deve conter 10 ou 11 dígitos numéricos');
    }
    
    // Validar email se fornecido
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respostaJson(false, 'Email inválido');
    }
    
    try {
        // Verificar se CNPJ já existe
        $stmt = $pdo->prepare("SELECT id FROM fornecedores WHERE cnpj = ?");
        $stmt->execute([$cnpj]);
        if ($stmt->fetch()) {
            respostaJson(false, 'CNPJ já cadastrado');
        }
        
        // Inserir fornecedor
        $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj, telefone, email, endereco) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $cnpj, $telefone, $email, $endereco]);
        
        respostaJson(true, 'Fornecedor adicionado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao adicionar fornecedor: ' . $e->getMessage());
    }
}

function editarFornecedor() {
    global $pdo;
    
    $id = intval($_POST['id'] ?? 0);
    $nome = limparDados($_POST['nome'] ?? '');
    $cnpj = limparDados($_POST['cnpj'] ?? '');
    $telefone = limparDados($_POST['telefone'] ?? '');
    $email = limparDados($_POST['email'] ?? '');
    $endereco = limparDados($_POST['endereco'] ?? '');
    
    if ($id <= 0) {
        respostaJson(false, 'ID do fornecedor inválido');
    }
    
    if (empty($nome) || empty($cnpj) || empty($telefone)) {
        respostaJson(false, 'Nome, CNPJ e telefone são obrigatórios');
    }
    
    // Validar CNPJ
    if (strlen($cnpj) !== 14 || !is_numeric($cnpj)) {
        respostaJson(false, 'CNPJ deve conter 14 dígitos numéricos');
    }
    
    // Validar telefone
    if (strlen($telefone) < 10 || strlen($telefone) > 11 || !is_numeric($telefone)) {
        respostaJson(false, 'Telefone deve conter 10 ou 11 dígitos numéricos');
    }
    
    // Validar email se fornecido
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respostaJson(false, 'Email inválido');
    }
    
    try {
        // Verificar se fornecedor existe
        $stmt = $pdo->prepare("SELECT id FROM fornecedores WHERE id = ? AND ativo = 1");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            respostaJson(false, 'Fornecedor não encontrado');
        }
        
        // Verificar se CNPJ já existe em outro fornecedor
        $stmt = $pdo->prepare("SELECT id FROM fornecedores WHERE cnpj = ? AND id != ?");
        $stmt->execute([$cnpj, $id]);
        if ($stmt->fetch()) {
            respostaJson(false, 'CNPJ já cadastrado para outro fornecedor');
        }
        
        // Atualizar fornecedor
        $stmt = $pdo->prepare("UPDATE fornecedores SET nome = ?, cnpj = ?, telefone = ?, email = ?, endereco = ? WHERE id = ?");
        $stmt->execute([$nome, $cnpj, $telefone, $email, $endereco, $id]);
        
        respostaJson(true, 'Fornecedor atualizado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao atualizar fornecedor: ' . $e->getMessage());
    }
}

function excluirFornecedor() {
    global $pdo;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        respostaJson(false, 'ID do fornecedor inválido');
    }
    
    try {
        // Verificar se fornecedor existe
        $stmt = $pdo->prepare("SELECT id FROM fornecedores WHERE id = ? AND ativo = 1");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            respostaJson(false, 'Fornecedor não encontrado');
        }
        
        // Marcar como inativo (soft delete)
        $stmt = $pdo->prepare("UPDATE fornecedores SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        respostaJson(true, 'Fornecedor excluído com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao excluir fornecedor: ' . $e->getMessage());
    }
}
?>
