<?php
/**
 * Sistema de Gerenciamento de Empréstimos
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
        case 'emprestar':
            handleEmprestar();
            break;
        case 'devolver':
            handleDevolver();
            break;
        case 'renovar':
            handleRenovar();
            break;
        case 'listar':
            handleListar();
            break;
        case 'buscar':
            handleBuscar();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
} catch (Exception $e) {
    error_log("Erro no sistema de empréstimos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}

/**
 * Função para lidar com empréstimo de livro
 */
function handleEmprestar() {
    $usuario_id = (int) ($_POST['usuario_id'] ?? 0);
    $livro_id = (int) ($_POST['livro_id'] ?? 0);
    
    if (!$usuario_id || !$livro_id) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        return;
    }
    
    // Verificar se usuário existe e está ativo
    $sql = "SELECT id, nome, ativo, tem_debito FROM usuarios WHERE id = ? AND ativo = TRUE";
    $usuario = dbFetchOne($sql, [$usuario_id]);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado ou inativo']);
        return;
    }
    
    // Verificar se usuário tem débito
    if ($usuario['tem_debito']) {
        echo json_encode(['success' => false, 'message' => 'Usuário possui débito pendente']);
        return;
    }
    
    // Verificar se livro existe e está disponível
    $sql = "SELECT id, titulo, estoque_disponivel, ativo FROM livros WHERE id = ? AND ativo = TRUE";
    $livro = dbFetchOne($sql, [$livro_id]);
    
    if (!$livro) {
        echo json_encode(['success' => false, 'message' => 'Livro não encontrado ou inativo']);
        return;
    }
    
    if ($livro['estoque_disponivel'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Livro não disponível para empréstimo']);
        return;
    }
    
    // Verificar limite de empréstimos
    $limite = getConfig('limite_emprestimos_usuario', 5);
    $sql = "SELECT COUNT(*) as total FROM emprestimos WHERE usuario_id = ? AND status IN ('emprestado', 'atrasado')";
    $emprestimos_ativos = dbFetchOne($sql, [$usuario_id]);
    
    if ($emprestimos_ativos['total'] >= $limite) {
        echo json_encode(['success' => false, 'message' => "Limite de $limite empréstimos atingido"]);
        return;
    }
    
    // Verificar se usuário já tem este livro emprestado
    $sql = "SELECT id FROM emprestimos WHERE usuario_id = ? AND livro_id = ? AND status IN ('emprestado', 'atrasado')";
    $ja_emprestado = dbFetchOne($sql, [$usuario_id, $livro_id]);
    
    if ($ja_emprestado) {
        echo json_encode(['success' => false, 'message' => 'Você já possui este livro emprestado']);
        return;
    }
    
    // Calcular data de devolução
    $prazo_dias = getConfig('prazo_emprestimo_dias', 7);
    $data_devolucao = date('Y-m-d', strtotime("+$prazo_dias days"));
    
    // Inserir empréstimo
    $sql = "INSERT INTO emprestimos (usuario_id, livro_id, data_devolucao_prevista, status) 
            VALUES (?, ?, ?, 'emprestado')";
    
    $emprestimo_id = dbInsert($sql, [$usuario_id, $livro_id, $data_devolucao]);
    
    if ($emprestimo_id) {
        // Registrar atividade
        logActivity($usuario_id, 'emprestimo', "Empréstimo do livro: {$livro['titulo']}", [
            'emprestimo_id' => $emprestimo_id,
            'livro_id' => $livro_id,
            'data_devolucao' => $data_devolucao
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Livro emprestado com sucesso!',
            'data_devolucao' => formatDate($data_devolucao),
            'emprestimo_id' => $emprestimo_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao realizar empréstimo']);
    }
}

/**
 * Função para lidar com devolução de livro
 */
function handleDevolver() {
    $emprestimo_id = (int) ($_POST['emprestimo_id'] ?? 0);
    $usuario_id = (int) ($_POST['usuario_id'] ?? 0);
    
    if (!$emprestimo_id || !$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        return;
    }
    
    // Verificar se empréstimo existe e pertence ao usuário
    $sql = "SELECT e.id, e.usuario_id, e.livro_id, e.status, e.data_devolucao_prevista, 
                   l.titulo, u.nome as usuario_nome
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            JOIN usuarios u ON e.usuario_id = u.id
            WHERE e.id = ? AND e.usuario_id = ?";
    
    $emprestimo = dbFetchOne($sql, [$emprestimo_id, $usuario_id]);
    
    if (!$emprestimo) {
        echo json_encode(['success' => false, 'message' => 'Empréstimo não encontrado']);
        return;
    }
    
    if ($emprestimo['status'] === 'devolvido') {
        echo json_encode(['success' => false, 'message' => 'Livro já foi devolvido']);
        return;
    }
    
    // Calcular multa se houver atraso
    $multa_valor = 0;
    $dias_atraso = 0;
    
    if (strtotime($emprestimo['data_devolucao_prevista']) < time()) {
        $dias_atraso = floor((time() - strtotime($emprestimo['data_devolucao_prevista'])) / (24 * 60 * 60));
        $valor_multa_diaria = getConfig('valor_multa_diaria', 2.00);
        $multa_valor = $dias_atraso * $valor_multa_diaria;
    }
    
    // Atualizar empréstimo
    $sql = "UPDATE emprestimos SET 
            status = 'devolvido', 
            data_devolucao_real = CURRENT_TIMESTAMP,
            multa_valor = ?,
            multa_paga = ?
            WHERE id = ?";
    
    $multa_paga = $multa_valor > 0 ? false : true;
    
    if (dbUpdate($sql, [$multa_valor, $multa_paga, $emprestimo_id]) > 0) {
        // Se há multa, criar registro na tabela de multas
        if ($multa_valor > 0) {
            $sql = "INSERT INTO multas (emprestimo_id, valor, motivo, status) 
                    VALUES (?, ?, ?, 'pendente')";
            dbInsert($sql, [
                $emprestimo_id, 
                $multa_valor, 
                "Multa por atraso de $dias_atraso dias"
            ]);
            
            // Atualizar status de débito do usuário
            $sql = "UPDATE usuarios SET tem_debito = TRUE WHERE id = ?";
            dbUpdate($sql, [$usuario_id]);
        }
        
        // Registrar atividade
        logActivity($usuario_id, 'devolucao', "Devolução do livro: {$emprestimo['titulo']}", [
            'emprestimo_id' => $emprestimo_id,
            'livro_id' => $emprestimo['livro_id'],
            'multa_valor' => $multa_valor,
            'dias_atraso' => $dias_atraso
        ]);
        
        $response = [
            'success' => true,
            'message' => 'Livro devolvido com sucesso!',
            'multa_valor' => $multa_valor,
            'dias_atraso' => $dias_atraso
        ];
        
        if ($multa_valor > 0) {
            $response['message'] .= " Multa de " . formatMoney($multa_valor) . " gerada.";
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao processar devolução']);
    }
}

/**
 * Função para lidar com renovação de empréstimo
 */
function handleRenovar() {
    $emprestimo_id = (int) ($_POST['emprestimo_id'] ?? 0);
    $usuario_id = (int) ($_POST['usuario_id'] ?? 0);
    
    if (!$emprestimo_id || !$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        return;
    }
    
    // Verificar se empréstimo existe e pertence ao usuário
    $sql = "SELECT e.id, e.usuario_id, e.livro_id, e.status, e.renovado, e.data_emprestimo,
                   l.titulo, u.nome as usuario_nome
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            JOIN usuarios u ON e.usuario_id = u.id
            WHERE e.id = ? AND e.usuario_id = ? AND e.status = 'emprestado'";
    
    $emprestimo = dbFetchOne($sql, [$emprestimo_id, $usuario_id]);
    
    if (!$emprestimo) {
        echo json_encode(['success' => false, 'message' => 'Empréstimo não encontrado ou não pode ser renovado']);
        return;
    }
    
    if ($emprestimo['renovado']) {
        echo json_encode(['success' => false, 'message' => 'Este empréstimo já foi renovado']);
        return;
    }
    
    // Verificar se já se passaram os dias mínimos para renovação
    $dias_para_renovacao = getConfig('dias_para_renovacao', 6);
    $dias_passados = floor((time() - strtotime($emprestimo['data_emprestimo'])) / (24 * 60 * 60));
    
    if ($dias_passados < $dias_para_renovacao) {
        $dias_restantes = $dias_para_renovacao - $dias_passados;
        echo json_encode(['success' => false, 'message' => "Renovação disponível em $dias_restantes dias"]);
        return;
    }
    
    // Calcular nova data de devolução
    $prazo_dias = getConfig('prazo_emprestimo_dias', 7);
    $nova_data_devolucao = date('Y-m-d', strtotime("+$prazo_dias days"));
    
    // Atualizar empréstimo
    $sql = "UPDATE emprestimos SET 
            data_devolucao_prevista = ?, 
            renovado = TRUE 
            WHERE id = ?";
    
    if (dbUpdate($sql, [$nova_data_devolucao, $emprestimo_id]) > 0) {
        // Registrar atividade
        logActivity($usuario_id, 'renovacao', "Renovação do livro: {$emprestimo['titulo']}", [
            'emprestimo_id' => $emprestimo_id,
            'livro_id' => $emprestimo['livro_id'],
            'nova_data_devolucao' => $nova_data_devolucao
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Empréstimo renovado com sucesso!',
            'nova_data_devolucao' => formatDate($nova_data_devolucao)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao renovar empréstimo']);
    }
}

/**
 * Função para listar empréstimos do usuário
 */
function handleListar() {
    $usuario_id = (int) ($_POST['usuario_id'] ?? 0);
    $status = $_POST['status'] ?? 'ativos';
    
    if (!$usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Usuário não especificado']);
        return;
    }
    
    $where_status = '';
    $params = [$usuario_id];
    
    switch ($status) {
        case 'ativos':
            $where_status = "AND e.status IN ('emprestado', 'atrasado')";
            break;
        case 'devolvidos':
            $where_status = "AND e.status = 'devolvido'";
            break;
        case 'todos':
            // Não adiciona filtro
            break;
        default:
            $where_status = "AND e.status IN ('emprestado', 'atrasado')";
    }
    
    $sql = "SELECT e.id, e.data_emprestimo, e.data_devolucao_prevista, e.data_devolucao_real,
                   e.status, e.renovado, e.multa_valor, e.multa_paga,
                   l.titulo, l.imagem_capa, a.nome as autor
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            LEFT JOIN autores a ON l.autor_id = a.id
            WHERE e.usuario_id = ? $where_status
            ORDER BY e.data_emprestimo DESC";
    
    $emprestimos = dbFetchAll($sql, $params);
    
    // Formatar dados para exibição
    foreach ($emprestimos as &$emprestimo) {
        $emprestimo['data_emprestimo_formatada'] = formatDate($emprestimo['data_emprestimo']);
        $emprestimo['data_devolucao_prevista_formatada'] = formatDate($emprestimo['data_devolucao_prevista']);
        $emprestimo['data_devolucao_real_formatada'] = $emprestimo['data_devolucao_real'] ? 
            formatDate($emprestimo['data_devolucao_real']) : null;
        $emprestimo['multa_formatada'] = $emprestimo['multa_valor'] > 0 ? 
            formatMoney($emprestimo['multa_valor']) : null;
        
        // Calcular dias de atraso se aplicável
        if ($emprestimo['status'] === 'emprestado' && strtotime($emprestimo['data_devolucao_prevista']) < time()) {
            $emprestimo['dias_atraso'] = floor((time() - strtotime($emprestimo['data_devolucao_prevista'])) / (24 * 60 * 60));
        }
    }
    
    echo json_encode([
        'success' => true,
        'emprestimos' => $emprestimos,
        'total' => count($emprestimos)
    ]);
}

/**
 * Função para buscar empréstimos (admin)
 */
function handleBuscar() {
    $filtro = $_POST['filtro'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $status = $_POST['status'] ?? '';
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($filtro) && !empty($valor)) {
        switch ($filtro) {
            case 'usuario':
                $where_conditions[] = "u.nome LIKE ?";
                $params[] = "%$valor%";
                break;
            case 'cpf':
                $where_conditions[] = "u.cpf LIKE ?";
                $params[] = "%$valor%";
                break;
            case 'livro':
                $where_conditions[] = "l.titulo LIKE ?";
                $params[] = "%$valor%";
                break;
        }
    }
    
    if (!empty($status)) {
        $where_conditions[] = "e.status = ?";
        $params[] = $status;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $sql = "SELECT e.id, e.data_emprestimo, e.data_devolucao_prevista, e.data_devolucao_real,
                   e.status, e.renovado, e.multa_valor, e.multa_paga,
                   l.titulo, l.imagem_capa, a.nome as autor,
                   u.nome as usuario_nome, u.cpf, u.telefone
            FROM emprestimos e
            JOIN livros l ON e.livro_id = l.id
            LEFT JOIN autores a ON l.autor_id = a.id
            JOIN usuarios u ON e.usuario_id = u.id
            $where_clause
            ORDER BY e.data_emprestimo DESC
            LIMIT 100";
    
    $emprestimos = dbFetchAll($sql, $params);
    
    // Formatar dados
    foreach ($emprestimos as &$emprestimo) {
        $emprestimo['data_emprestimo_formatada'] = formatDate($emprestimo['data_emprestimo']);
        $emprestimo['data_devolucao_prevista_formatada'] = formatDate($emprestimo['data_devolucao_prevista']);
        $emprestimo['data_devolucao_real_formatada'] = $emprestimo['data_devolucao_real'] ? 
            formatDate($emprestimo['data_devolucao_real']) : null;
        $emprestimo['multa_formatada'] = $emprestimo['multa_valor'] > 0 ? 
            formatMoney($emprestimo['multa_valor']) : null;
    }
    
    echo json_encode([
        'success' => true,
        'emprestimos' => $emprestimos,
        'total' => count($emprestimos)
    ]);
}

/**
 * Função para obter estatísticas de empréstimos
 */
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

/**
 * Função para calcular multas automaticamente
 */
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

/**
 * Função para obter livros mais emprestados
 */
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


