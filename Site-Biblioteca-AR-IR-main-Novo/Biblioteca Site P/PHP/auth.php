<?php
require_once 'config.php';

header('Content-Type: application/json');

// Função para registrar novo usuário
function registrarUsuario($dados) {
    try {
        $pdo = conectarDB();
        
        // Validar dados obrigatórios
        if (empty($dados['nome']) || empty($dados['cpf']) || empty($dados['telefone']) || empty($dados['senha'])) {
            return ['success' => false, 'message' => 'Todos os campos são obrigatórios'];
        }
        
        // Validar CPF
        if (!validarCPF($dados['cpf'])) {
            return ['success' => false, 'message' => 'CPF inválido'];
        }
        
        // Verificar se CPF já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $stmt->execute([$dados['cpf']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'CPF já cadastrado'];
        }
        
        // Hash da senha
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        // Inserir usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, cpf, telefone, email, senha, tipo, status) 
            VALUES (?, ?, ?, ?, ?, 'Usuário', 'Ativo')
        ");
        
        $stmt->execute([
            $dados['nome'],
            $dados['cpf'],
            $dados['telefone'],
            $dados['email'] ?? null,
            $senha_hash
        ]);
        
        $usuario_id = $pdo->lastInsertId();
        
        // Log da atividade
        logAtividade($usuario_id, 'Registro de usuário', 'Novo usuário registrado');
        
        return ['success' => true, 'message' => 'Usuário registrado com sucesso', 'usuario_id' => $usuario_id];
        
    } catch (Exception $e) {
        error_log("Erro no registro: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para fazer login
function fazerLogin($dados) {
    try {
        $pdo = conectarDB();
        
        // Validar dados obrigatórios
        if (empty($dados['cpf']) || empty($dados['senha']) || empty($dados['telefone'])) {
            return ['success' => false, 'message' => 'CPF, senha e telefone são obrigatórios'];
        }
        
        // Buscar usuário
        $stmt = $pdo->prepare("
            SELECT id, nome, cpf, telefone, email, senha, tipo, is_admin, status, tem_debito, tem_doacao_pendente 
            FROM usuarios 
            WHERE cpf = ? AND telefone = ? AND status = 'Ativo'
        ");
        
        $stmt->execute([$dados['cpf'], $dados['telefone']]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'CPF, telefone ou senha inválidos'];
        }
        
        // Verificar senha
        if (!password_verify($dados['senha'], $usuario['senha'])) {
            return ['success' => false, 'message' => 'CPF, telefone ou senha inválidos'];
        }
        
        // Verificar se usuário está ativo
        if ($usuario['status'] !== 'Ativo') {
            return ['success' => false, 'message' => 'Usuário inativo ou suspenso'];
        }
        
        // Atualizar último acesso
        $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        
        // Criar sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_cpf'] = $usuario['cpf'];
        $_SESSION['usuario_telefone'] = $usuario['telefone'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['is_admin'] = $usuario['is_admin'];
        $_SESSION['tem_debito'] = $usuario['tem_debito'];
        $_SESSION['tem_doacao_pendente'] = $usuario['tem_doacao_pendente'];
        $_SESSION['login_time'] = time();
        
        // Log da atividade
        logAtividade($usuario['id'], 'Login', 'Usuário fez login no sistema');
        
        return [
            'success' => true, 
            'message' => 'Login realizado com sucesso',
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'tipo' => $usuario['tipo'],
                'is_admin' => $usuario['is_admin'],
                'tem_debito' => $usuario['tem_debito'],
                'tem_doacao_pendente' => $usuario['tem_doacao_pendente']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro no login: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para fazer logout
function fazerLogout() {
    if (isset($_SESSION['usuario_id'])) {
        logAtividade($_SESSION['usuario_id'], 'Logout', 'Usuário fez logout do sistema');
    }
    
    session_destroy();
    return ['success' => true, 'message' => 'Logout realizado com sucesso'];
}

// Função para recuperar senha
function recuperarSenha($dados) {
    try {
        $pdo = conectarDB();
        
        // Validar dados obrigatórios
        if (empty($dados['cpf']) || empty($dados['telefone']) || empty($dados['nova_senha'])) {
            return ['success' => false, 'message' => 'Todos os campos são obrigatórios'];
        }
        
        // Validar CPF
        if (!validarCPF($dados['cpf'])) {
            return ['success' => false, 'message' => 'CPF inválido'];
        }
        
        // Buscar usuário
        $stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE cpf = ? AND telefone = ? AND status = 'Ativo'");
        $stmt->execute([$dados['cpf'], $dados['telefone']]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'CPF ou telefone não encontrados'];
        }
        
        // Hash da nova senha
        $nova_senha_hash = password_hash($dados['nova_senha'], PASSWORD_DEFAULT);
        
        // Atualizar senha
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->execute([$nova_senha_hash, $usuario['id']]);
        
        // Log da atividade
        logAtividade($usuario['id'], 'Recuperação de senha', 'Senha alterada via recuperação');
        
        // Enviar email de confirmação (se configurado)
        if ($usuario['email'] && function_exists('mail')) {
            $assunto = SITE_NAME . " - Senha Alterada";
            $mensagem = "
                <h2>Senha Alterada com Sucesso</h2>
                <p>Olá {$usuario['nome']},</p>
                <p>Sua senha foi alterada com sucesso.</p>
                <p>Se você não solicitou esta alteração, entre em contato conosco imediatamente.</p>
                <p>Atenciosamente,<br>" . SITE_NAME . "</p>
            ";
            
            enviarEmail($usuario['email'], $assunto, $mensagem);
        }
        
        return ['success' => true, 'message' => 'Senha alterada com sucesso'];
        
    } catch (Exception $e) {
        error_log("Erro na recuperação de senha: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para verificar se usuário está logado
function verificarStatusLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        return ['success' => false, 'message' => 'Usuário não logado'];
    }
    
    try {
        $pdo = conectarDB();
        
        // Buscar dados atualizados do usuário
        $stmt = $pdo->prepare("
            SELECT id, nome, cpf, telefone, email, tipo, is_admin, status, tem_debito, tem_doacao_pendente 
            FROM usuarios 
            WHERE id = ? AND status = 'Ativo'
        ");
        
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            session_destroy();
            return ['success' => false, 'message' => 'Usuário não encontrado ou inativo'];
        }
        
        // Atualizar dados da sessão
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['tem_debito'] = $usuario['tem_debito'];
        $_SESSION['tem_doacao_pendente'] = $usuario['tem_doacao_pendente'];
        
        return [
            'success' => true, 
            'message' => 'Usuário logado',
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'tipo' => $usuario['tipo'],
                'is_admin' => $usuario['is_admin'],
                'tem_debito' => $usuario['tem_debito'],
                'tem_doacao_pendente' => $usuario['tem_doacao_pendente']
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro na verificação de login: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Processar requisições
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'registrar':
        $resultado = registrarUsuario($_POST);
        break;
        
    case 'login':
        $resultado = fazerLogin($_POST);
        break;
        
    case 'logout':
        $resultado = fazerLogout();
        break;
        
    case 'recuperar_senha':
        $resultado = recuperarSenha($_POST);
        break;
        
    case 'verificar_login':
        $resultado = verificarStatusLogin();
        break;
        
    default:
        $resultado = ['success' => false, 'message' => 'Ação não especificada'];
}

echo json_encode($resultado);
?>
