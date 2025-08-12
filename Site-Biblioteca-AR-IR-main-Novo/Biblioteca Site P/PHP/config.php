<?php
// Configurações da base de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'biblioteca_arco_iris');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações gerais
define('SITE_NAME', 'Biblioteca Arco-Íris');
define('SITE_URL', 'http://localhost/biblioteca');
define('UPLOAD_PATH', '../IMG/');

// Configurações de sessão
session_start();

// Função para conectar à base de dados
function conectarDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

// Função para verificar se o usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.html');
        exit();
    }
    return $_SESSION['usuario_id'];
}

// Função para verificar se o usuário é admin
function verificarAdmin() {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        header('Location: ../login.html');
        exit();
    }
    return $_SESSION['usuario_id'];
}

// Função para sanitizar dados
function sanitizar($dados) {
    return htmlspecialchars(strip_tags(trim($dados)));
}

// Função para gerar token CSRF
function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para verificar token CSRF
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Função para log de atividades
function logAtividade($usuario_id, $acao, $detalhes = '') {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("INSERT INTO logs (usuario_id, acao, detalhes, data_hora) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$usuario_id, $acao, $detalhes]);
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

// Função para enviar email
function enviarEmail($para, $assunto, $mensagem) {
    $headers = "From: " . SITE_NAME . " <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
    $headers .= "Reply-To: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($para, $assunto, $mensagem, $headers);
}

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) {
        return false;
    }
    
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

// Função para validar CNPJ
function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }
    
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
        return false;
    }
    
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
}

// Função para formatar data
function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

// Função para formatar data e hora
function formatarDataHora($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Função para calcular dias de atraso
function calcularAtraso($data_emprestimo) {
    $data_emprestimo = new DateTime($data_emprestimo);
    $data_emprestimo->add(new DateInterval('P7D')); // 7 dias de prazo
    $hoje = new DateTime();
    
    if ($hoje > $data_emprestimo) {
        return $hoje->diff($data_emprestimo)->days;
    }
    return 0;
}

// Função para calcular multa
function calcularMulta($dias_atraso) {
    $valor_dia = 2.00; // R$ 2,00 por dia de atraso
    return $dias_atraso * $valor_dia;
}
?>
