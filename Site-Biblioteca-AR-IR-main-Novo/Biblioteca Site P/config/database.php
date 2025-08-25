<?php
/**
 * Configuração do Banco de Dados
 * Biblioteca Arco-Íris
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'biblioteca_arco_iris');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Classe Database - Singleton para conexão com banco de dados
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

/**
 * Funções auxiliares para operações no banco de dados
 */

// Executar query simples
function dbQuery($sql, $params = []) {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Buscar um registro
function dbFetchOne($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetch();
}

// Buscar todos os registros
function dbFetchAll($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}

// Inserir registro e retornar ID
function dbInsert($sql, $params = []) {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $pdo->lastInsertId();
}

// Atualizar registro
function dbUpdate($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->rowCount();
}

// Deletar registro
function dbDelete($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->rowCount();
}

// Iniciar transação
function dbBeginTransaction() {
    $pdo = Database::getInstance()->getConnection();
    return $pdo->beginTransaction();
}

// Confirmar transação
function dbCommit() {
    $pdo = Database::getInstance()->getConnection();
    return $pdo->commit();
}

// Reverter transação
function dbRollback() {
    $pdo = Database::getInstance()->getConnection();
    return $pdo->rollback();
}

/**
 * Funções de validação e sanitização
 */

// Sanitizar input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validar CPF brasileiro
function validateCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1+$/', $cpf)) {
        return false;
    }
    
    // Calcula os dígitos verificadores
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

// Validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Funções de senha
 */

// Hash de senha
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verificar senha
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Funções de token
 */

// Gerar token aleatório
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Funções de log de atividades
 */

// Registrar atividade
function logActivity($usuario_id, $tipo_acao, $descricao, $dados_json = null) {
    $sql = "INSERT INTO historico_atividades (usuario_id, tipo_acao, descricao, dados_json, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $dados_json_str = $dados_json ? json_encode($dados_json) : null;
    
    return dbInsert($sql, [$usuario_id, $tipo_acao, $descricao, $dados_json_str, $ip, $user_agent]);
}

/**
 * Funções de configuração do sistema
 */

// Obter configuração
function getConfig($chave, $valor_padrao = null) {
    $sql = "SELECT valor, tipo FROM configuracoes WHERE chave = ?";
    $config = dbFetchOne($sql, [$chave]);
    
    if (!$config) {
        return $valor_padrao;
    }
    
    // Converter valor baseado no tipo
    switch ($config['tipo']) {
        case 'integer':
            return (int) $config['valor'];
        case 'boolean':
            return (bool) $config['valor'];
        case 'decimal':
            return (float) $config['valor'];
        case 'json':
            return json_decode($config['valor'], true);
        default:
            return $config['valor'];
    }
}

// Definir configuração
function setConfig($chave, $valor, $descricao = null, $tipo = 'string') {
    $sql = "INSERT INTO configuracoes (chave, valor, descricao, tipo) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            valor = VALUES(valor), 
            descricao = VALUES(descricao), 
            tipo = VALUES(tipo)";
    
    return dbUpdate($sql, [$chave, $valor, $descricao, $tipo]);
}

/**
 * Funções de formatação
 */

// Formatar data
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

// Formatar data e hora
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

// Formatar moeda
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Funções de sessão e autenticação
 */

// Verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Verificar se usuário é admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Redirecionar com mensagem
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit;
}

// Mostrar mensagem
function showMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        
        // Limpar mensagem da sessão
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        $class = match($type) {
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-success'
        };
        
        return "<div class='alert $class'>$message</div>";
    }
    return '';
}

/**
 * Funções de estatísticas do usuário
 */

// Obter estatísticas do usuário
function getEstatisticasUsuario($usuario_id) {
    $sql = "SELECT 
                total_emprestimos,
                total_devolvidos,
                (SELECT COUNT(*) FROM emprestimos WHERE usuario_id = ? AND status = 'emprestado') as emprestimos_ativos,
                (SELECT COUNT(*) FROM emprestimos WHERE usuario_id = ? AND status = 'atrasado') as emprestimos_atrasados
            FROM usuarios 
            WHERE id = ?";
    
    return dbFetchOne($sql, [$usuario_id, $usuario_id, $usuario_id]);
}

/**
 * Funções de estatísticas gerais
 */

// Obter estatísticas de empréstimos
function getEstatisticasEmprestimos() {
    $sql = "SELECT 
                COUNT(*) as total_emprestimos,
                COUNT(CASE WHEN status = 'emprestado' THEN 1 END) as emprestados,
                COUNT(CASE WHEN status = 'devolvido' THEN 1 END) as devolvidos,
                COUNT(CASE WHEN status = 'atrasado' THEN 1 END) as atrasados,
                SUM(CASE WHEN multa_valor > 0 THEN multa_valor ELSE 0 END) as total_multas
            FROM emprestimos";
    
    return dbFetchOne($sql);
}

// Calcular multas automaticamente
function calcularMultasAutomaticas() {
    $sql = "UPDATE emprestimos SET status = 'atrasado' 
            WHERE status = 'emprestado' 
            AND data_devolucao_prevista < CURRENT_DATE";
    
    $atrasados = dbUpdate($sql);
    
    if ($atrasados > 0) {
        // Atualizar status de débito dos usuários
        $sql = "UPDATE usuarios u 
                SET tem_debito = TRUE 
                WHERE EXISTS (
                    SELECT 1 FROM emprestimos e 
                    WHERE e.usuario_id = u.id 
                    AND e.status = 'atrasado'
                )";
        dbUpdate($sql);
    }
    
    return $atrasados;
}

// Obter livros mais emprestados
function getLivrosMaisEmprestados($limite = 10) {
    $sql = "SELECT l.titulo, a.nome as autor, COUNT(e.id) as total_emprestimos
            FROM livros l
            LEFT JOIN autores a ON l.autor_id = a.id
            LEFT JOIN emprestimos e ON l.id = e.livro_id
            WHERE l.ativo = TRUE
            GROUP BY l.id, l.titulo, a.nome
            ORDER BY total_emprestimos DESC
            LIMIT ?";
    
    return dbFetchAll($sql, [$limite]);
}
?>

