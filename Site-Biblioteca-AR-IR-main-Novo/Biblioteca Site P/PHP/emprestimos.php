<?php
require_once 'config.php';

header('Content-Type: application/json');

// Função para fazer empréstimo
function fazerEmprestimo($dados) {
    try {
        $usuario_id = verificarLogin();
        $pdo = conectarDB();
        
        // Validar dados obrigatórios
        if (empty($dados['livro_id'])) {
            return ['success' => false, 'message' => 'ID do livro é obrigatório'];
        }
        
        $livro_id = $dados['livro_id'];
        
        // Verificar se livro existe e está disponível
        $stmt = $pdo->prepare("SELECT id, titulo, disponiveis, estoque FROM livros WHERE id = ? AND status = 'Disponível'");
        $stmt->execute([$livro_id]);
        $livro = $stmt->fetch();
        
        if (!$livro) {
            return ['success' => false, 'message' => 'Livro não encontrado ou indisponível'];
        }
        
        if ($livro['disponiveis'] < 1) {
            return ['success' => false, 'message' => 'Livro não disponível para empréstimo'];
        }
        
        // Verificar se usuário já tem este livro emprestado
        $stmt = $pdo->prepare("
            SELECT id FROM emprestimos 
            WHERE usuario_id = ? AND livro_id = ? AND status IN ('Emprestado', 'Renovado')
        ");
        $stmt->execute([$usuario_id, $livro_id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Você já possui este livro emprestado'];
        }
        
        // Verificar limite de empréstimos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM emprestimos 
            WHERE usuario_id = ? AND status IN ('Emprestado', 'Renovado')
        ");
        $stmt->execute([$usuario_id]);
        $total_emprestimos = $stmt->fetch()['total'];
        
        $config = buscarConfiguracao('max_emprestimos');
        $limite_emprestimos = $config ? (int)$config : 5;
        
        if ($total_emprestimos >= $limite_emprestimos) {
            return ['success' => false, 'message' => "Você já possui o máximo de $limite_emprestimos empréstimos ativos"];
        }
        
        // Verificar se usuário tem débitos
        $stmt = $pdo->prepare("SELECT tem_debito FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if ($usuario['tem_debito']) {
            return ['success' => false, 'message' => 'Você possui débitos pendentes. Regularize-os antes de fazer novos empréstimos'];
        }
        
        // Calcular data de devolução
        $config_prazo = buscarConfiguracao('prazo_emprestimo');
        $prazo_dias = $config_prazo ? (int)$config_prazo : 7;
        $data_devolucao = date('Y-m-d', strtotime("+$prazo_dias days"));
        
        // Inserir empréstimo
        $stmt = $pdo->prepare("
            INSERT INTO emprestimos (
                usuario_id, livro_id, data_emprestimo, data_devolucao_prevista, 
                status, funcionario_id
            ) VALUES (?, ?, NOW(), ?, 'Emprestado', ?)
        ");
        
        $funcionario_id = $_SESSION['is_admin'] ? $_SESSION['usuario_id'] : null;
        $stmt->execute([$usuario_id, $livro_id, $data_devolucao, $funcionario_id]);
        
        $emprestimo_id = $pdo->lastInsertId();
        
        // Atualizar disponibilidade do livro
        $stmt = $pdo->prepare("UPDATE livros SET disponiveis = disponiveis - 1 WHERE id = ?");
        $stmt->execute([$livro_id]);
        
        // Log da atividade
        logAtividade($usuario_id, 'Empréstimo', "Livro '{$livro['titulo']}' emprestado");
        
        return [
            'success' => true, 
            'message' => 'Empréstimo realizado com sucesso',
            'emprestimo_id' => $emprestimo_id,
            'data_devolucao' => formatarData($data_devolucao)
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao fazer empréstimo: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para renovar empréstimo
function renovarEmprestimo($emprestimo_id) {
    try {
        $usuario_id = verificarLogin();
        $pdo = conectarDB();
        
        // Verificar se empréstimo existe e pertence ao usuário
        $stmt = $pdo->prepare("
            SELECT e.*, l.titulo 
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            WHERE e.id = ? AND e.usuario_id = ? AND e.status IN ('Emprestado', 'Renovado')
        ");
        $stmt->execute([$emprestimo_id, $usuario_id]);
        $emprestimo = $stmt->fetch();
        
        if (!$emprestimo) {
            return ['success' => false, 'message' => 'Empréstimo não encontrado ou não pode ser renovado'];
        }
        
        // Verificar limite de renovações
        $config = buscarConfiguracao('renovacoes_max');
        $limite_renovacoes = $config ? (int)$config : 2;
        
        if ($emprestimo['renovacoes'] >= $limite_renovacoes) {
            return ['success' => false, 'message' => 'Limite de renovações atingido'];
        }
        
        // Verificar se não está atrasado
        $dias_atraso = calcularAtraso($emprestimo['data_devolucao_prevista']);
        if ($dias_atraso > 0) {
            return ['success' => false, 'message' => 'Não é possível renovar empréstimo em atraso'];
        }
        
        // Calcular nova data de devolução
        $config_prazo = buscarConfiguracao('prazo_emprestimo');
        $prazo_dias = $config_prazo ? (int)$config_prazo : 7;
        $nova_data_devolucao = date('Y-m-d', strtotime("+$prazo_dias days"));
        
        // Atualizar empréstimo
        $stmt = $pdo->prepare("
            UPDATE emprestimos 
            SET status = 'Renovado', renovacoes = renovacoes + 1, 
                data_devolucao_prevista = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$nova_data_devolucao, $emprestimo_id]);
        
        // Log da atividade
        logAtividade($usuario_id, 'Renovação', "Empréstimo do livro '{$emprestimo['titulo']}' renovado");
        
        return [
            'success' => true, 
            'message' => 'Empréstimo renovado com sucesso',
            'nova_data_devolucao' => formatarData($nova_data_devolucao)
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao renovar empréstimo: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para devolver livro
function devolverLivro($emprestimo_id) {
    try {
        $usuario_id = verificarLogin();
        $pdo = conectarDB();
        
        // Verificar se empréstimo existe
        $stmt = $pdo->prepare("
            SELECT e.*, l.titulo, l.id as livro_id
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            WHERE e.id = ? AND e.status IN ('Emprestado', 'Renovado')
        ");
        $stmt->execute([$emprestimo_id]);
        $emprestimo = $stmt->fetch();
        
        if (!$emprestimo) {
            return ['success' => false, 'message' => 'Empréstimo não encontrado ou já devolvido'];
        }
        
        // Verificar se usuário é o dono do empréstimo ou admin
        if ($emprestimo['usuario_id'] != $usuario_id && !$_SESSION['is_admin']) {
            return ['success' => false, 'message' => 'Você não tem permissão para devolver este livro'];
        }
        
        // Calcular multa se houver atraso
        $dias_atraso = calcularAtraso($emprestimo['data_devolucao_prevista']);
        $multa = 0;
        
        if ($dias_atraso > 0) {
            $config = buscarConfiguracao('multa_dia');
            $valor_dia = $config ? (float)$config : 2.00;
            $multa = calcularMulta($dias_atraso);
            
            // Inserir multa
            $stmt = $pdo->prepare("
                INSERT INTO multas (
                    usuario_id, emprestimo_id, valor, dias_atraso, status
                ) VALUES (?, ?, ?, ?, 'Pendente')
            ");
            $stmt->execute([$emprestimo['usuario_id'], $emprestimo_id, $multa, $dias_atraso]);
            
            // Marcar usuário como tendo débito
            $stmt = $pdo->prepare("UPDATE usuarios SET tem_debito = TRUE WHERE id = ?");
            $stmt->execute([$emprestimo['usuario_id']]);
        }
        
        // Atualizar empréstimo
        $stmt = $pdo->prepare("
            UPDATE emprestimos 
            SET status = 'Devolvido', data_devolucao_real = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$emprestimo_id]);
        
        // Atualizar disponibilidade do livro
        $stmt = $pdo->prepare("UPDATE livros SET disponiveis = disponiveis + 1 WHERE id = ?");
        $stmt->execute([$emprestimo['livro_id']]);
        
        // Log da atividade
        $acao = $dias_atraso > 0 ? 'Devolução com atraso' : 'Devolução';
        logAtividade($usuario_id, $acao, "Livro '{$emprestimo['titulo']}' devolvido");
        
        $mensagem = 'Livro devolvido com sucesso';
        if ($multa > 0) {
            $mensagem .= ". Multa de R$ " . number_format($multa, 2, ',', '.') . " gerada por $dias_atraso dia(s) de atraso";
        }
        
        return [
            'success' => true, 
            'message' => $mensagem,
            'multa' => $multa,
            'dias_atraso' => $dias_atraso
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao devolver livro: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para listar empréstimos do usuário
function listarEmprestimosUsuario($filtros = []) {
    try {
        $usuario_id = verificarLogin();
        $pdo = conectarDB();
        
        $where = "WHERE e.usuario_id = ?";
        $params = [$usuario_id];
        
        // Filtros
        if (!empty($filtros['status'])) {
            $where .= " AND e.status = ?";
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['busca'])) {
            $where .= " AND (l.titulo LIKE ? OR l.autor LIKE ?)";
            $busca = "%{$filtros['busca']}%";
            $params[] = $busca;
            $params[] = $busca;
        }
        
        // Paginação
        $pagina = $filtros['pagina'] ?? 1;
        $por_pagina = $filtros['por_pagina'] ?? 20;
        $offset = ($pagina - 1) * $por_pagina;
        
        // Contar total
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            $where
        ");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Buscar empréstimos
        $stmt = $pdo->prepare("
            SELECT 
                e.*,
                l.titulo,
                l.autor,
                l.imagem,
                DATEDIFF(CURDATE(), e.data_devolucao_prevista) as dias_atraso
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            $where
            ORDER BY e.data_emprestimo DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $por_pagina;
        $params[] = $offset;
        $stmt->execute($params);
        $emprestimos = $stmt->fetchAll();
        
        // Processar dados
        foreach ($emprestimos as &$emprestimo) {
            $emprestimo['data_emprestimo_formatada'] = formatarDataHora($emprestimo['data_emprestimo']);
            $emprestimo['data_devolucao_formatada'] = formatarData($emprestimo['data_devolucao_prevista']);
            $emprestimo['pode_renovar'] = $emprestimo['status'] == 'Emprestado' && $emprestimo['dias_atraso'] <= 0;
            $emprestimo['pode_devolver'] = in_array($emprestimo['status'], ['Emprestado', 'Renovado']);
        }
        
        return [
            'success' => true,
            'emprestimos' => $emprestimos,
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => ceil($total / $por_pagina)
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao listar empréstimos: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para listar todos os empréstimos (admin)
function listarTodosEmprestimos($filtros = []) {
    try {
        verificarAdmin();
        $pdo = conectarDB();
        
        $where = "WHERE 1=1";
        $params = [];
        
        // Filtros
        if (!empty($filtros['status'])) {
            $where .= " AND e.status = ?";
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['usuario'])) {
            $where .= " AND (u.nome LIKE ? OR u.cpf LIKE ?)";
            $busca = "%{$filtros['usuario']}%";
            $params[] = $busca;
            $params[] = $busca;
        }
        
        if (!empty($filtros['livro'])) {
            $where .= " AND (l.titulo LIKE ? OR l.autor LIKE ?)";
            $busca = "%{$filtros['livro']}%";
            $params[] = $busca;
            $params[] = $busca;
        }
        
        if (!empty($filtros['data_inicio'])) {
            $where .= " AND DATE(e.data_emprestimo) >= ?";
            $params[] = $filtros['data_inicio'];
        }
        
        if (!empty($filtros['data_fim'])) {
            $where .= " AND DATE(e.data_emprestimo) <= ?";
            $params[] = $filtros['data_fim'];
        }
        
        // Paginação
        $pagina = $filtros['pagina'] ?? 1;
        $por_pagina = $filtros['por_pagina'] ?? 20;
        $offset = ($pagina - 1) * $por_pagina;
        
        // Contar total
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM emprestimos e
            JOIN usuarios u ON e.usuario_id = u.id
            JOIN livros l ON e.livro_id = l.id
            $where
        ");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Buscar empréstimos
        $stmt = $pdo->prepare("
            SELECT 
                e.*,
                u.nome as nome_usuario,
                u.cpf as cpf_usuario,
                l.titulo,
                l.autor,
                DATEDIFF(CURDATE(), e.data_devolucao_prevista) as dias_atraso
            FROM emprestimos e
            JOIN usuarios u ON e.usuario_id = u.id
            JOIN livros l ON e.livro_id = l.id
            $where
            ORDER BY e.data_emprestimo DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $por_pagina;
        $params[] = $offset;
        $stmt->execute($params);
        $emprestimos = $stmt->fetchAll();
        
        // Processar dados
        foreach ($emprestimos as &$emprestimo) {
            $emprestimo['data_emprestimo_formatada'] = formatarDataHora($emprestimo['data_emprestimo']);
            $emprestimo['data_devolucao_formatada'] = formatarData($emprestimo['data_devolucao_prevista']);
            $emprestimo['pode_renovar'] = $emprestimo['status'] == 'Emprestado' && $emprestimo['dias_atraso'] <= 0;
            $emprestimo['pode_devolver'] = in_array($emprestimo['status'], ['Emprestado', 'Renovado']);
        }
        
        return [
            'success' => true,
            'emprestimos' => $emprestimos,
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => ceil($total / $por_pagina)
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao listar todos os empréstimos: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar configuração
function buscarConfiguracao($chave) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
        $stmt->execute([$chave]);
        $resultado = $stmt->fetch();
        return $resultado ? $resultado['valor'] : null;
    } catch (Exception $e) {
        return null;
    }
}

// Processar requisições
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'fazer_emprestimo':
        $resultado = fazerEmprestimo($_POST);
        break;
        
    case 'renovar':
        $emprestimo_id = $_POST['emprestimo_id'] ?? 0;
        $resultado = renovarEmprestimo($emprestimo_id);
        break;
        
    case 'devolver':
        $emprestimo_id = $_POST['emprestimo_id'] ?? 0;
        $resultado = devolverLivro($emprestimo_id);
        break;
        
    case 'listar_usuario':
        $resultado = listarEmprestimosUsuario($_GET);
        break;
        
    case 'listar_todos':
        $resultado = listarTodosEmprestimos($_GET);
        break;
        
    default:
        $resultado = ['success' => false, 'message' => 'Ação não especificada'];
}

echo json_encode($resultado);
?>

