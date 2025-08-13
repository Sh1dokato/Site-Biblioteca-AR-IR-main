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
    case 'estatisticas_gerais':
        estatisticasGerais();
        break;
    case 'livros_mais_emprestados':
        livrosMaisEmprestados();
        break;
    case 'usuarios_mais_ativos':
        usuariosMaisAtivos();
        break;
    case 'emprestimos_por_periodo':
        emprestimosPorPeriodo();
        break;
    case 'multas_por_periodo':
        multasPorPeriodo();
        break;
    case 'doacoes_por_periodo':
        doacoesPorPeriodo();
        break;
    default:
        respostaJson(false, 'Ação não reconhecida');
}

function estatisticasGerais() {
    global $pdo;
    
    verificarAdmin();
    
    try {
        // Total de usuários
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE ativo = 1");
        $total_usuarios = $stmt->fetch()['total'];
        
        // Total de livros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM livros WHERE ativo = 1");
        $total_livros = $stmt->fetch()['total'];
        
        // Total de exemplares
        $stmt = $pdo->query("SELECT SUM(quantidade_total) as total FROM livros WHERE ativo = 1");
        $total_exemplares = $stmt->fetch()['total'] ?? 0;
        
        // Exemplares disponíveis
        $stmt = $pdo->query("SELECT SUM(quantidade_disponivel) as total FROM livros WHERE ativo = 1");
        $exemplares_disponiveis = $stmt->fetch()['total'] ?? 0;
        
        // Empréstimos ativos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM emprestimos WHERE status IN ('ativo', 'atrasado')");
        $emprestimos_ativos = $stmt->fetch()['total'];
        
        // Empréstimos atrasados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM emprestimos WHERE status = 'atrasado'");
        $emprestimos_atrasados = $stmt->fetch()['total'];
        
        // Total de multas pendentes
        $stmt = $pdo->query("SELECT SUM(multa) as total FROM emprestimos WHERE multa_paga = 0 AND multa > 0");
        $multas_pendentes = $stmt->fetch()['total'] ?? 0;
        
        // Doações pendentes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM doacoes WHERE status = 'pendente'");
        $doacoes_pendentes = $stmt->fetch()['total'];
        
        $estatisticas = [
            'total_usuarios' => $total_usuarios,
            'total_livros' => $total_livros,
            'total_exemplares' => $total_exemplares,
            'exemplares_disponiveis' => $exemplares_disponiveis,
            'emprestimos_ativos' => $emprestimos_ativos,
            'emprestimos_atrasados' => $emprestimos_atrasados,
            'multas_pendentes' => $multas_pendentes,
            'doacoes_pendentes' => $doacoes_pendentes
        ];
        
        respostaJson(true, 'Estatísticas geradas com sucesso', $estatisticas);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao gerar estatísticas: ' . $e->getMessage());
    }
}

function livrosMaisEmprestados() {
    global $pdo;
    
    verificarAdmin();
    
    $limite = intval($_GET['limite'] ?? 10);
    
    try {
        $stmt = $pdo->prepare("
            SELECT l.titulo, l.autor, COUNT(e.id) as total_emprestimos
            FROM livros l
            LEFT JOIN emprestimos e ON l.id = e.livro_id
            WHERE l.ativo = 1
            GROUP BY l.id, l.titulo, l.autor
            ORDER BY total_emprestimos DESC
            LIMIT ?
        ");
        $stmt->execute([$limite]);
        $livros = $stmt->fetchAll();
        
        respostaJson(true, 'Livros mais emprestados listados com sucesso', $livros);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar livros mais emprestados: ' . $e->getMessage());
    }
}

function usuariosMaisAtivos() {
    global $pdo;
    
    verificarAdmin();
    
    $limite = intval($_GET['limite'] ?? 10);
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.nome, COUNT(e.id) as total_emprestimos
            FROM usuarios u
            LEFT JOIN emprestimos e ON u.id = e.usuario_id
            WHERE u.ativo = 1
            GROUP BY u.id, u.nome
            ORDER BY total_emprestimos DESC
            LIMIT ?
        ");
        $stmt->execute([$limite]);
        $usuarios = $stmt->fetchAll();
        
        respostaJson(true, 'Usuários mais ativos listados com sucesso', $usuarios);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar usuários mais ativos: ' . $e->getMessage());
    }
}

function emprestimosPorPeriodo() {
    global $pdo;
    
    verificarAdmin();
    
    $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Primeiro dia do mês atual
    $data_fim = $_GET['data_fim'] ?? date('Y-m-d'); // Hoje
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(data_emprestimo) as data,
                COUNT(*) as total_emprestimos,
                COUNT(CASE WHEN status = 'devolvido' THEN 1 END) as devolvidos,
                COUNT(CASE WHEN status = 'atrasado' THEN 1 END) as atrasados
            FROM emprestimos
            WHERE DATE(data_emprestimo) BETWEEN ? AND ?
            GROUP BY DATE(data_emprestimo)
            ORDER BY data
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $emprestimos = $stmt->fetchAll();
        
        respostaJson(true, 'Empréstimos por período listados com sucesso', $emprestimos);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar empréstimos por período: ' . $e->getMessage());
    }
}

function multasPorPeriodo() {
    global $pdo;
    
    verificarAdmin();
    
    $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
    $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(data_devolucao_real) as data,
                COUNT(*) as total_multas,
                SUM(multa) as valor_total_multas,
                AVG(multa) as valor_medio_multa
            FROM emprestimos
            WHERE DATE(data_devolucao_real) BETWEEN ? AND ? 
                AND multa > 0
            GROUP BY DATE(data_devolucao_real)
            ORDER BY data
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $multas = $stmt->fetchAll();
        
        respostaJson(true, 'Multas por período listadas com sucesso', $multas);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar multas por período: ' . $e->getMessage());
    }
}

function doacoesPorPeriodo() {
    global $pdo;
    
    verificarAdmin();
    
    $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
    $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(data_doacao) as data,
                COUNT(*) as total_doacoes,
                COUNT(CASE WHEN tipo_doacao = 'livro' THEN 1 END) as doacoes_livros,
                COUNT(CASE WHEN tipo_doacao = 'item_higiene' THEN 1 END) as doacoes_higiene,
                COUNT(CASE WHEN tipo_doacao = 'dinheiro' THEN 1 END) as doacoes_dinheiro,
                SUM(CASE WHEN tipo_doacao = 'dinheiro' THEN valor ELSE 0 END) as valor_total_dinheiro
            FROM doacoes
            WHERE DATE(data_doacao) BETWEEN ? AND ?
            GROUP BY DATE(data_doacao)
            ORDER BY data
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $doacoes = $stmt->fetchAll();
        
        respostaJson(true, 'Doações por período listadas com sucesso', $doacoes);
    } catch (PDOException $e) {
        respostaJson(false, 'Erro ao listar doações por período: ' . $e->getMessage());
    }
}
?>
