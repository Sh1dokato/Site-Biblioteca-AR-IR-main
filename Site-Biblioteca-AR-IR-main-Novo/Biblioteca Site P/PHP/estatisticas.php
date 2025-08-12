<?php
require_once 'config.php';

header('Content-Type: application/json');

// Função para buscar estatísticas gerais
function buscarEstatisticasGerais() {
    try {
        verificarAdmin(); // Apenas admins podem ver estatísticas gerais
        $pdo = conectarDB();
        
        // Total de livros
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM livros");
        $stmt->execute();
        $total_livros = $stmt->fetch()['total'];
        
        // Livros disponíveis
        $stmt = $pdo->prepare("SELECT SUM(disponiveis) as total FROM livros");
        $stmt->execute();
        $livros_disponiveis = $stmt->fetch()['total'] ?? 0;
        
        // Livros emprestados
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emprestimos WHERE status IN ('Emprestado', 'Renovado')");
        $stmt->execute();
        $livros_emprestados = $stmt->fetch()['total'];
        
        // Total de usuários
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE status = 'Ativo'");
        $stmt->execute();
        $total_usuarios = $stmt->fetch()['total'];
        
        // Empréstimos hoje
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emprestimos WHERE DATE(data_emprestimo) = CURDATE()");
        $stmt->execute();
        $emprestimos_hoje = $stmt->fetch()['total'];
        
        // Devoluções hoje
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emprestimos WHERE DATE(data_devolucao_real) = CURDATE()");
        $stmt->execute();
        $devolucoes_hoje = $stmt->fetch()['total'];
        
        // Empréstimos em atraso
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM emprestimos e
            WHERE e.status IN ('Emprestado', 'Renovado') 
            AND e.data_devolucao_prevista < CURDATE()
        ");
        $stmt->execute();
        $emprestimos_atraso = $stmt->fetch()['total'];
        
        // Total de multas pendentes
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(valor) as valor_total FROM multas WHERE status = 'Pendente'");
        $stmt->execute();
        $multas_info = $stmt->fetch();
        $total_multas = $multas_info['total'];
        $valor_multas = $multas_info['valor_total'] ?? 0;
        
        // Taxa de ocupação
        $taxa_ocupacao = $total_livros > 0 ? round(($livros_emprestados / $total_livros) * 100, 2) : 0;
        
        return [
            'success' => true,
            'estatisticas' => [
                'total_livros' => $total_livros,
                'livros_disponiveis' => $livros_disponiveis,
                'livros_emprestados' => $livros_emprestados,
                'total_usuarios' => $total_usuarios,
                'emprestimos_hoje' => $emprestimos_hoje,
                'devolucoes_hoje' => $devolucoes_hoje,
                'emprestimos_atraso' => $emprestimos_atraso,
                'total_multas' => $total_multas,
                'valor_multas' => $valor_multas,
                'taxa_ocupacao' => $taxa_ocupacao
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar estatísticas: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar livros mais populares
function buscarLivrosPopulares($limite = 10) {
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            SELECT 
                l.id,
                l.titulo,
                l.autor,
                l.categoria,
                l.imagem,
                l.disponiveis,
                COUNT(e.id) as total_emprestimos
            FROM livros l
            LEFT JOIN emprestimos e ON l.id = e.livro_id
            GROUP BY l.id
            ORDER BY total_emprestimos DESC, l.titulo
            LIMIT ?
        ");
        
        $stmt->execute([$limite]);
        $livros = $stmt->fetchAll();
        
        return ['success' => true, 'livros' => $livros];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar livros populares: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar empréstimos por mês
function buscarEmprestimosPorMes($ano = null) {
    try {
        $pdo = conectarDB();
        
        if (!$ano) {
            $ano = date('Y');
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                MONTH(data_emprestimo) as mes,
                COUNT(*) as total
            FROM emprestimos 
            WHERE YEAR(data_emprestimo) = ?
            GROUP BY MONTH(data_emprestimo)
            ORDER BY mes
        ");
        
        $stmt->execute([$ano]);
        $dados = $stmt->fetchAll();
        
        // Preencher meses sem empréstimos
        $meses_completos = [];
        for ($i = 1; $i <= 12; $i++) {
            $mes_encontrado = false;
            foreach ($dados as $dado) {
                if ($dado['mes'] == $i) {
                    $meses_completos[] = [
                        'mes' => $i,
                        'mes_nome' => nomeMes($i),
                        'total' => $dado['total']
                    ];
                    $mes_encontrado = true;
                    break;
                }
            }
            if (!$mes_encontrado) {
                $meses_completos[] = [
                    'mes' => $i,
                    'mes_nome' => nomeMes($i),
                    'total' => 0
                ];
            }
        }
        
        return ['success' => true, 'dados' => $meses_completos, 'ano' => $ano];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar empréstimos por mês: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar empréstimos por categoria
function buscarEmprestimosPorCategoria() {
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            SELECT 
                l.categoria,
                COUNT(e.id) as total_emprestimos,
                COUNT(DISTINCT l.id) as total_livros
            FROM livros l
            LEFT JOIN emprestimos e ON l.id = e.livro_id
            WHERE l.categoria IS NOT NULL AND l.categoria != ''
            GROUP BY l.categoria
            ORDER BY total_emprestimos DESC
        ");
        
        $stmt->execute();
        $categorias = $stmt->fetchAll();
        
        return ['success' => true, 'categorias' => $categorias];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar empréstimos por categoria: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar status dos empréstimos
function buscarStatusEmprestimos() {
    try {
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as total
            FROM emprestimos 
            GROUP BY status
            ORDER BY total DESC
        ");
        
        $stmt->execute();
        $status = $stmt->fetchAll();
        
        // Adicionar empréstimos em atraso
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM emprestimos e
            WHERE e.status IN ('Emprestado', 'Renovado') 
            AND e.data_devolucao_prevista < CURDATE()
        ");
        $stmt->execute();
        $atrasados = $stmt->fetch()['total'];
        
        if ($atrasados > 0) {
            $status[] = [
                'status' => 'Atrasado',
                'total' => $atrasados
            ];
        }
        
        return ['success' => true, 'status' => $status];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar status dos empréstimos: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar top usuários
function buscarTopUsuarios($limite = 10) {
    try {
        verificarAdmin(); // Apenas admins podem ver estatísticas de usuários
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.nome,
                u.cpf,
                COUNT(e.id) as total_emprestimos,
                COUNT(CASE WHEN e.status = 'Devolvido' THEN 1 END) as total_devolvidos,
                COUNT(CASE WHEN e.status IN ('Emprestado', 'Renovado') THEN 1 END) as emprestimos_ativos
            FROM usuarios u
            LEFT JOIN emprestimos e ON u.id = e.usuario_id
            WHERE u.status = 'Ativo'
            GROUP BY u.id
            ORDER BY total_emprestimos DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limite]);
        $usuarios = $stmt->fetchAll();
        
        return ['success' => true, 'usuarios' => $usuarios];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar top usuários: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar relatório de atrasos
function buscarRelatorioAtrasos() {
    try {
        verificarAdmin(); // Apenas admins podem ver relatório de atrasos
        $pdo = conectarDB();
        
        $stmt = $pdo->prepare("
            SELECT 
                e.id,
                e.data_emprestimo,
                e.data_devolucao_prevista,
                e.renovacoes,
                u.nome as nome_usuario,
                u.cpf,
                u.telefone,
                l.titulo as titulo_livro,
                l.autor,
                DATEDIFF(CURDATE(), e.data_devolucao_prevista) as dias_atraso
            FROM emprestimos e
            JOIN usuarios u ON e.usuario_id = u.id
            JOIN livros l ON e.livro_id = l.id
            WHERE e.status IN ('Emprestado', 'Renovado') 
            AND e.data_devolucao_prevista < CURDATE()
            ORDER BY dias_atraso DESC
        ");
        
        $stmt->execute();
        $atrasos = $stmt->fetchAll();
        
        // Calcular multas
        foreach ($atrasos as &$atraso) {
            $atraso['multa'] = calcularMulta($atraso['dias_atraso']);
            $atraso['data_emprestimo_formatada'] = formatarData($atraso['data_emprestimo']);
            $atraso['data_devolucao_formatada'] = formatarData($atraso['data_devolucao_prevista']);
        }
        
        return ['success' => true, 'atrasos' => $atrasos];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar relatório de atrasos: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função para buscar estatísticas por período
function buscarEstatisticasPorPeriodo($data_inicio, $data_fim) {
    try {
        verificarAdmin(); // Apenas admins podem ver estatísticas por período
        $pdo = conectarDB();
        
        // Empréstimos no período
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM emprestimos 
            WHERE DATE(data_emprestimo) BETWEEN ? AND ?
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $emprestimos_periodo = $stmt->fetch()['total'];
        
        // Devoluções no período
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM emprestimos 
            WHERE DATE(data_devolucao_real) BETWEEN ? AND ?
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $devolucoes_periodo = $stmt->fetch()['total'];
        
        // Novos usuários no período
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM usuarios 
            WHERE DATE(data_cadastro) BETWEEN ? AND ?
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $novos_usuarios = $stmt->fetch()['total'];
        
        // Multas geradas no período
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total, SUM(valor) as valor_total FROM multas 
            WHERE DATE(data_geracao) BETWEEN ? AND ?
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $multas_info = $stmt->fetch();
        $multas_periodo = $multas_info['total'];
        $valor_multas_periodo = $multas_info['valor_total'] ?? 0;
        
        // Livros mais emprestados no período
        $stmt = $pdo->prepare("
            SELECT 
                l.titulo,
                l.autor,
                COUNT(e.id) as total_emprestimos
            FROM livros l
            JOIN emprestimos e ON l.id = e.livro_id
            WHERE DATE(e.data_emprestimo) BETWEEN ? AND ?
            GROUP BY l.id
            ORDER BY total_emprestimos DESC
            LIMIT 5
        ");
        $stmt->execute([$data_inicio, $data_fim]);
        $livros_populares_periodo = $stmt->fetchAll();
        
        return [
            'success' => true,
            'estatisticas' => [
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim,
                'emprestimos_periodo' => $emprestimos_periodo,
                'devolucoes_periodo' => $devolucoes_periodo,
                'novos_usuarios' => $novos_usuarios,
                'multas_periodo' => $multas_periodo,
                'valor_multas_periodo' => $valor_multas_periodo,
                'livros_populares_periodo' => $livros_populares_periodo
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao buscar estatísticas por período: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro interno do servidor'];
    }
}

// Função auxiliar para nome do mês
function nomeMes($numero) {
    $meses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
    return $meses[$numero] ?? 'Mês ' . $numero;
}

// Processar requisições
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'estatisticas_gerais':
        $resultado = buscarEstatisticasGerais();
        break;
        
    case 'livros_populares':
        $limite = $_GET['limite'] ?? 10;
        $resultado = buscarLivrosPopulares($limite);
        break;
        
    case 'emprestimos_por_mes':
        $ano = $_GET['ano'] ?? null;
        $resultado = buscarEmprestimosPorMes($ano);
        break;
        
    case 'emprestimos_por_categoria':
        $resultado = buscarEmprestimosPorCategoria();
        break;
        
    case 'status_emprestimos':
        $resultado = buscarStatusEmprestimos();
        break;
        
    case 'top_usuarios':
        $limite = $_GET['limite'] ?? 10;
        $resultado = buscarTopUsuarios($limite);
        break;
        
    case 'relatorio_atrasos':
        $resultado = buscarRelatorioAtrasos();
        break;
        
    case 'estatisticas_periodo':
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        $resultado = buscarEstatisticasPorPeriodo($data_inicio, $data_fim);
        break;
        
    default:
        $resultado = ['success' => false, 'message' => 'Ação não especificada'];
}

echo json_encode($resultado);
?>
