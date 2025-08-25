<?php
/**
 * Sistema de Autenticação
 * Biblioteca Arco-Íris
 */

require_once '../config/database.php';

header('Content-Type: application/json');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$acao = $_POST['acao'] ?? '';

try {
    switch ($acao) {
        case 'login':
            handleLogin();
            break;
        case 'registrar':
            handleRegistro();
            break;
        case 'logout':
            handleLogout();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
} catch (Exception $e) {
    error_log("Erro na autenticação: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}

/**
 * Função para lidar com login
 */
function handleLogin() {
    $cpf = sanitizeInput($_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $telefone = sanitizeInput($_POST['telefone'] ?? '');
    
    // Validações
    if (empty($cpf) || empty($senha) || empty($telefone)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
        return;
    }
    
    if (!validateCPF($cpf)) {
        echo json_encode(['success' => false, 'message' => 'CPF inválido']);
        return;
    }
    
    // Buscar usuário no banco
    $sql = "SELECT id, nome, cpf, telefone, senha, email, is_admin, ativo, tem_debito, tem_doacao_pendente 
            FROM usuarios 
            WHERE cpf = ? AND telefone = ? AND ativo = TRUE";
    
    $usuario = dbFetchOne($sql, [$cpf, $telefone]);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'CPF, telefone ou senha incorretos']);
        return;
    }
    
    // Verificar senha
    if (!verifyPassword($senha, $usuario['senha'])) {
        echo json_encode(['success' => false, 'message' => 'CPF, telefone ou senha incorretos']);
        return;
    }
    
    // Atualizar último acesso
    $sql = "UPDATE usuarios SET ultimo_acesso = CURRENT_TIMESTAMP WHERE id = ?";
    dbUpdate($sql, [$usuario['id']]);
    
    // Registrar atividade
    logActivity($usuario['id'], 'login', 'Login realizado com sucesso');
    
    // Preparar resposta
    $response = [
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'usuario' => [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'cpf' => $usuario['cpf'],
            'telefone' => $usuario['telefone'],
            'email' => $usuario['email'],
            'is_admin' => (bool) $usuario['is_admin'],
            'tem_debito' => (bool) $usuario['tem_debito'],
            'tem_doacao_pendente' => (bool) $usuario['tem_doacao_pendente']
        ]
    ];
    
    echo json_encode($response);
}

/**
 * Função para lidar com registro
 */
function handleRegistro() {
    $nome = sanitizeInput($_POST['nome'] ?? '');
    $cpf = sanitizeInput($_POST['cpf'] ?? '');
    $telefone = sanitizeInput($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $email = sanitizeInput($_POST['email'] ?? '');
    
    // Validações
    if (empty($nome) || empty($cpf) || empty($telefone) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
        return;
    }
    
    if (!validateCPF($cpf)) {
        echo json_encode(['success' => false, 'message' => 'CPF inválido']);
        return;
    }
    
    if (strlen($senha) < 6) {
        echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres']);
        return;
    }
    
    if (!empty($email) && !validateEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        return;
    }
    
    // Verificar se CPF já existe
    $sql = "SELECT id FROM usuarios WHERE cpf = ?";
    $existente = dbFetchOne($sql, [$cpf]);
    
    if ($existente) {
        echo json_encode(['success' => false, 'message' => 'CPF já cadastrado']);
        return;
    }
    
    // Verificar se telefone já existe
    $sql = "SELECT id FROM usuarios WHERE telefone = ?";
    $existente = dbFetchOne($sql, [$telefone]);
    
    if ($existente) {
        echo json_encode(['success' => false, 'message' => 'Telefone já cadastrado']);
        return;
    }
    
    // Hash da senha
    $senha_hash = hashPassword($senha);
    
    // Inserir usuário
    $sql = "INSERT INTO usuarios (nome, cpf, telefone, senha, email, is_admin, ativo) 
            VALUES (?, ?, ?, ?, ?, FALSE, TRUE)";
    
    $usuario_id = dbInsert($sql, [$nome, $cpf, $telefone, $senha_hash, $email]);
    
    if ($usuario_id) {
        // Registrar atividade
        logActivity($usuario_id, 'registro', 'Novo usuário registrado');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Usuário registrado com sucesso! Faça login para continuar.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao registrar usuário']);
    }
}

/**
 * Função para lidar com logout
 */
function handleLogout() {
    if (isLoggedIn()) {
        logActivity($_SESSION['usuario_id'], 'logout', 'Logout realizado');
    }
    
    // Limpar sessão
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso']);
}

/**
 * Função para verificar se usuário tem empréstimos atrasados
 */
function verificarEmprestimosAtrasados($usuario_id) {
    $sql = "SELECT COUNT(*) as total 
            FROM emprestimos 
            WHERE usuario_id = ? AND status = 'atrasado'";
    
    $resultado = dbFetchOne($sql, [$usuario_id]);
    return $resultado['total'] > 0;
}

/**
 * Função para verificar se usuário tem limite de empréstimos
 */
function verificarLimiteEmprestimos($usuario_id) {
    $limite = getConfig('limite_emprestimos_usuario', 5);
    
    $sql = "SELECT COUNT(*) as total 
            FROM emprestimos 
            WHERE usuario_id = ? AND status IN ('emprestado', 'atrasado')";
    
    $resultado = dbFetchOne($sql, [$usuario_id]);
    return $resultado['total'] < $limite;
}

/**
 * Função para obter estatísticas do usuário
 */
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
 * Função para atualizar dados do usuário
 */
function atualizarDadosUsuario($usuario_id, $dados) {
    $campos_permitidos = ['nome', 'email', 'telefone'];
    $campos_update = [];
    $valores = [];
    
    foreach ($dados as $campo => $valor) {
        if (in_array($campo, $campos_permitidos)) {
            $campos_update[] = "$campo = ?";
            $valores[] = sanitizeInput($valor);
        }
    }
    
    if (empty($campos_update)) {
        return false;
    }
    
    $valores[] = $usuario_id;
    $sql = "UPDATE usuarios SET " . implode(', ', $campos_update) . " WHERE id = ?";
    
    return dbUpdate($sql, $valores) > 0;
}

/**
 * Função para alterar senha
 */
function alterarSenha($usuario_id, $senha_atual, $nova_senha) {
    // Verificar senha atual
    $sql = "SELECT senha FROM usuarios WHERE id = ?";
    $usuario = dbFetchOne($sql, [$usuario_id]);
    
    if (!$usuario || !verifyPassword($senha_atual, $usuario['senha'])) {
        return ['success' => false, 'message' => 'Senha atual incorreta'];
    }
    
    // Validar nova senha
    if (strlen($nova_senha) < 6) {
        return ['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres'];
    }
    
    // Atualizar senha
    $nova_senha_hash = hashPassword($nova_senha);
    $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
    
    if (dbUpdate($sql, [$nova_senha_hash, $usuario_id]) > 0) {
        logActivity($usuario_id, 'alteracao_senha', 'Senha alterada com sucesso');
        return ['success' => true, 'message' => 'Senha alterada com sucesso'];
    }
    
    return ['success' => false, 'message' => 'Erro ao alterar senha'];
}

/**
 * Função para recuperar senha (simulação)
 */
function recuperarSenha($cpf, $telefone) {
    if (!validateCPF($cpf)) {
        return ['success' => false, 'message' => 'CPF inválido'];
    }
    
    // Verificar se usuário existe
    $sql = "SELECT id, nome, email FROM usuarios WHERE cpf = ? AND telefone = ? AND ativo = TRUE";
    $usuario = dbFetchOne($sql, [$cpf, $telefone]);
    
    if (!$usuario) {
        return ['success' => false, 'message' => 'Usuário não encontrado'];
    }
    
    // Gerar token temporário (em produção, enviaria por email)
    $token = generateToken(16);
    
    // Aqui você implementaria o envio de email com o token
    // Por enquanto, apenas simula o processo
    
    logActivity($usuario['id'], 'recuperacao_senha', 'Solicitação de recuperação de senha');
    
    return [
        'success' => true, 
        'message' => 'Instruções de recuperação enviadas para o email cadastrado',
        'token_simulado' => $token // Apenas para demonstração
    ];
}
?>


