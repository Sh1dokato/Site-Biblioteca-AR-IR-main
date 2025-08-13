<?php
require_once 'config.php';

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$acao = $_POST['acao'] ?? '';

switch ($acao) {
    case 'login':
        fazerLogin();
        break;
    case 'registrar':
        registrarUsuario();
        break;
    case 'logout':
        fazerLogout();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function fazerLogin() {
    global $pdo;
    
    $cpf = limparDados($_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $telefone = limparDados($_POST['telefone'] ?? '');
    
    if (empty($cpf) || empty($senha) || empty($telefone)) {
        respostaJson(false, 'Todos os campos são obrigatórios');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = ? AND telefone = ? AND ativo = 1");
        $stmt->execute([$cpf, $telefone]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_start();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
            
            respostaJson(true, 'Login realizado com sucesso', [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'tipo_usuario' => $usuario['tipo_usuario']
            ]);
        } else {
            respostaJson(false, 'CPF, telefone ou senha incorretos');
        }
    } catch (PDOException $e) {
        respostaJson(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}

function registrarUsuario() {
    global $pdo;
    
    $cpf = limparDados($_POST['cpf'] ?? '');
    $telefone = limparDados($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $nome = limparDados($_POST['nome'] ?? '');
    
    if (empty($cpf) || empty($telefone) || empty($senha) || empty($nome)) {
        respostaJson(false, 'Todos os campos são obrigatórios');
    }
    
    // Validar CPF (apenas verificar se tem 11 dígitos)
    if (strlen($cpf) !== 11 || !is_numeric($cpf)) {
        respostaJson(false, 'CPF deve conter 11 dígitos numéricos');
    }
    
    // Validar telefone (apenas verificar se tem 10 ou 11 dígitos)
    if (strlen($telefone) < 10 || strlen($telefone) > 11 || !is_numeric($telefone)) {
        respostaJson(false, 'Telefone deve conter 10 ou 11 dígitos numéricos');
    }
    
    try {
        // Verificar se CPF já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $stmt->execute([$cpf]);
        if ($stmt->fetch()) {
            respostaJson(false, 'CPF já cadastrado');
        }
        
        // Verificar se telefone já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE telefone = ?");
        $stmt->execute([$telefone]);
        if ($stmt->fetch()) {
            respostaJson(false, 'Telefone já cadastrado');
        }
        
        // Hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir novo usuário
        $stmt = $pdo->prepare("INSERT INTO usuarios (cpf, telefone, senha, nome) VALUES (?, ?, ?, ?)");
        $stmt->execute([$cpf, $telefone, $senha_hash, $nome]);
        
        respostaJson(true, 'Usuário registrado com sucesso');
    } catch (PDOException $e) {
        respostaJson(false, 'Erro no banco de dados: ' . $e->getMessage());
    }
}

function fazerLogout() {
    session_start();
    session_destroy();
    respostaJson(true, 'Logout realizado com sucesso');
}
?>
