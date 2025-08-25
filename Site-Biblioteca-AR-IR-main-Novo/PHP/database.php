<?php
/**
 * Configuração de conexão com o banco de dados
 * Biblioteca Arco-Íris
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'biblioteca_arco_iris');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

/**
 * Classe para gerenciar conexão com o banco de dados
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new Exception("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém a instância única da classe (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtém a conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Executa uma consulta SQL
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Erro na execução da query: " . $e->getMessage());
        }
    }
    
    /**
     * Executa uma consulta e retorna uma linha
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Executa uma consulta e retorna todas as linhas
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Executa uma inserção e retorna o ID do último registro inserido
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    /**
     * Executa uma atualização e retorna o número de linhas afetadas
     */
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Executa uma exclusão e retorna o número de linhas afetadas
     */
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Verifica se está em uma transação
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
}

/**
 * Função helper para obter conexão rápida
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Função helper para executar queries
 */
function dbQuery($sql, $params = []) {
    return getDB()->query($sql, $params);
}

/**
 * Função helper para buscar uma linha
 */
function dbFetchOne($sql, $params = []) {
    return getDB()->fetchOne($sql, $params);
}

/**
 * Função helper para buscar todas as linhas
 */
function dbFetchAll($sql, $params = []) {
    return getDB()->fetchAll($sql, $params);
}

/**
 * Função helper para inserir dados
 */
function dbInsert($sql, $params = []) {
    return getDB()->insert($sql, $params);
}

/**
 * Função helper para atualizar dados
 */
function dbUpdate($sql, $params = []) {
    return getDB()->update($sql, $params);
}

/**
 * Função helper para deletar dados
 */
function dbDelete($sql, $params = []) {
    return getDB()->delete($sql, $params);
}

/**
 * Função para validar e sanitizar dados de entrada
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Função para validar CPF
 */
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

/**
 * Função para validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Função para gerar hash de senha
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Função para verificar senha
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Função para gerar token único
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Função para registrar log de atividades
 */
function logActivity($usuario_id, $tipo_acao, $descricao, $dados_json = null) {
    try {
        $sql = "INSERT INTO historico_atividades (usuario_id, tipo_acao, descricao, dados_json, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $usuario_id,
            $tipo_acao,
            $descricao,
            $dados_json ? json_encode($dados_json) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        return dbInsert($sql, $params);
    } catch (Exception $e) {
        error_log("Erro ao registrar atividade: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para obter configuração do sistema
 */
function getConfig($chave, $default = null) {
    try {
        $sql = "SELECT valor, tipo FROM configuracoes WHERE chave = ?";
        $config = dbFetchOne($sql, [$chave]);
        
        if (!$config) {
            return $default;
        }
        
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
    } catch (Exception $e) {
        error_log("Erro ao obter configuração: " . $e->getMessage());
        return $default;
    }
}

/**
 * Função para definir configuração do sistema
 */
function setConfig($chave, $valor, $descricao = null, $tipo = 'string') {
    try {
        $sql = "INSERT INTO configuracoes (chave, valor, descricao, tipo) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor), 
                descricao = VALUES(descricao), 
                tipo = VALUES(tipo)";
        
        return dbUpdate($sql, [$chave, $valor, $descricao, $tipo]);
    } catch (Exception $e) {
        error_log("Erro ao definir configuração: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para formatar data para exibição
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Função para formatar data e hora para exibição
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

/**
 * Função para formatar valor monetário
 */
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Função para verificar se usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Função para verificar se usuário é administrador
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Função para redirecionar com mensagem
 */
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit;
}

/**
 * Função para exibir mensagem
 */
function showMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';
        unset($_SESSION['message'], $_SESSION['message_type']);
        
        $class = match($type) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };
        
        return "<div class='alert $class'>$message</div>";
    }
    return '';
}

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

