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
        listarUsuarios();
        break;
    case 'editar':
        editarUsuario();
        break;
    case 'excluir':
        excluirUsuario();
        break;
    case 'alterar_senha':
        alterarSenha();
        break;
    case 'perfil':
        obterPerfil();
        break;
    case 'atualizar_perfil':
        atualizarPerfil();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function listarUsuarios() {
    global $pdo;
    
    verificarAdmin();
    
    try {
        $stmt = $pdo->query("
            SELECT id, cpf, telefone, nome, tipo_usuario, data_cadastro, ativo
            FROM usuarios 
            ORDER BY nome
        ");
        $usuarios = $stmt->fetchAll();
        
        respostaJson(true, 'Usuários listados com sucesso', $usuarios);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar usuários: ' . $e->getMessage());
    }
}

function editarUsuario() {
    global $pdo;
    
    verificarAdmin();
    
    $id = intval($_POST['id'] ?? 0);
    $nome = limparDados($_POST['nome'] ?? '');
    $cpf = limparDados($_POST['cpf'] ?? '');
    $telefone = limparDados($_POST['telefone'] ?? '');
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'usuario';
    $ativo = intval($_POST['ativo'] ?? 1);
    
    if ($id <= 0) {
        respostaJson(false, 'ID do usuário inválido');
    }
    
    if (empty($nome) || empty($cpf) || empty($telefone)) {
        respostaJson(false, 'Nome, CPF e telefone são obrigatórios');
    }
    
    if (!in_array($tipo_usuario, ['usuario', 'admin'])) {
        respostaJson(false, 'Tipo de usuário inválido');
    }
    
    // Validar CPF
    if (strlen($cpf) !== 11 || !is_numeric($cpf)) {
        respostaJson(false, 'CPF deve conter 11 dígitos numéricos');
    }
    
    // Validar telefone
    if (strlen($telefone) < 10 || strlen($telefone) > 11 || !is_numeric($telefone)) {
        respostaJson(false, 'Telefone deve conter 10 ou 11 dígitos numéricos');
    }
    
    try {
        // Verificar se usuário existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            respostaJson(false, 'Usuário não encontrado');
        }
        
        // Verificar se CPF já existe em outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ? AND id != ?");
        $stmt->execute([$cpf, $id]);
        if ($stmt->fetch()) {
            respostaJson(false, 'CPF já cadastrado para outro usuário');
        }
        
        // Verificar se telefone já existe em outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE telefone = ? AND id != ?");
        $stmt->execute([$telefone, $id]);
        if ($stmt->fetch()) {
            respostaJson(false, 'Telefone já cadastrado para outro usuário');
        }
        
        // Atualizar usuário
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nome = ?, cpf = ?, telefone = ?, tipo_usuario = ?, ativo = ?
            WHERE id = ?
        ");
        $stmt->execute([$nome, $cpf, $telefone, $tipo_usuario, $ativo, $id]);
        
        respostaJson(true, 'Usuário atualizado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao atualizar usuário: ' . $e->getMessage());
    }
}

function excluirUsuario() {
    global $pdo;
    
    verificarAdmin();
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        respostaJson(false, 'ID do usuário inválido');
    }
    
    try {
        // Verificar se usuário existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            respostaJson(false, 'Usuário não encontrado');
        }
        
        // Verificar se há empréstimos ativos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM emprestimos 
            WHERE usuario_id = ? AND status IN ('ativo', 'atrasado')
        ");
        $stmt->execute([$id]);
        $emprestimos = $stmt->fetch();
        
        if ($emprestimos['total'] > 0) {
            respostaJson(false, 'Não é possível excluir um usuário com empréstimos ativos');
        }
        
        // Marcar como inativo (soft delete)
        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        
        respostaJson(true, 'Usuário excluído com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao excluir usuário: ' . $e->getMessage());
    }
}

function alterarSenha() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        respostaJson(false, 'Todos os campos são obrigatórios');
    }
    
    if ($nova_senha !== $confirmar_senha) {
        respostaJson(false, 'Nova senha e confirmação não coincidem');
    }
    
    if (strlen($nova_senha) < 6) {
        respostaJson(false, 'Nova senha deve ter pelo menos 6 caracteres');
    }
    
    try {
        // Verificar senha atual
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
            respostaJson(false, 'Senha atual incorreta');
        }
        
        // Atualizar senha
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->execute([$nova_senha_hash, $usuario_id]);
        
        respostaJson(true, 'Senha alterada com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao alterar senha: ' . $e->getMessage());
    }
}

function obterPerfil() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, cpf, telefone, nome, tipo_usuario, data_cadastro
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            respostaJson(false, 'Usuário não encontrado');
        }
        
        respostaJson(true, 'Perfil obtido com sucesso', $usuario);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao obter perfil: ' . $e->getMessage());
    }
}

function atualizarPerfil() {
    global $pdo;
    
    $usuario_id = verificarLogin();
    $nome = limparDados($_POST['nome'] ?? '');
    $telefone = limparDados($_POST['telefone'] ?? '');
    
    if (empty($nome) || empty($telefone)) {
        respostaJson(false, 'Nome e telefone são obrigatórios');
    }
    
    // Validar telefone
    if (strlen($telefone) < 10 || strlen($telefone) > 11 || !is_numeric($telefone)) {
        respostaJson(false, 'Telefone deve conter 10 ou 11 dígitos numéricos');
    }
    
    try {
        // Verificar se telefone já existe em outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE telefone = ? AND id != ?");
        $stmt->execute([$telefone, $usuario_id]);
        if ($stmt->fetch()) {
            respostaJson(false, 'Telefone já cadastrado para outro usuário');
        }
        
        // Atualizar perfil
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, telefone = ? WHERE id = ?");
        $stmt->execute([$nome, $telefone, $usuario_id]);
        
        respostaJson(true, 'Perfil atualizado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao atualizar perfil: ' . $e->getMessage());
    }
}
?>
