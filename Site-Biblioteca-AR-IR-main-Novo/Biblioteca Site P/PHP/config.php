<?php
// Configuração do banco de dados
$host = 'localhost';
$dbname = 'biblioteca_arco_iris';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para limpar dados de entrada
function limparDados($dados) {
    $dados = trim($dados);
    $dados = stripslashes($dados);
    $dados = htmlspecialchars($dados);
    return $dados;
}

// Função para gerar resposta JSON
function respostaJson($sucesso, $mensagem, $dados = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $sucesso,
        'message' => $mensagem,
        'data' => $dados
    ]);
    exit;
}

// Função para verificar se usuário está logado
function verificarLogin() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        respostaJson(false, 'Usuário não está logado');
    }
    return $_SESSION['usuario_id'];
}

// Função para verificar se é admin
function verificarAdmin() {
    session_start();
    if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
        respostaJson(false, 'Acesso negado. Apenas administradores.');
    }
}
?>
