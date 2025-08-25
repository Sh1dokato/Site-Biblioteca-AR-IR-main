<?php
/**
 * Exemplo de Integração com Banco de Dados
 * Biblioteca Arco-Íris
 * 
 * Este arquivo demonstra como integrar o banco de dados MySQL
 * com as páginas existentes do sistema.
 */

require_once 'config/database.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    redirect('login.php', 'Faça login para acessar o sistema', 'error');
}

$usuario_id = $_SESSION['usuario_id'];
$is_admin = isAdmin();

// Obter dados do usuário logado
$sql = "SELECT id, nome, cpf, telefone, email, is_admin, tem_debito, tem_doacao_pendente 
        FROM usuarios WHERE id = ? AND ativo = TRUE";
$usuario = dbFetchOne($sql, [$usuario_id]);

if (!$usuario) {
    session_destroy();
    redirect('login.php', 'Usuário não encontrado', 'error');
}

// Obter estatísticas do usuário
$estatisticas = getEstatisticasUsuario($usuario_id);

// Obter empréstimos ativos do usuário
$sql = "SELECT e.id, e.data_emprestimo, e.data_devolucao_prevista, e.status, e.renovado,
               l.titulo, l.imagem_capa, a.nome as autor
        FROM emprestimos e
        JOIN livros l ON e.livro_id = l.id
        LEFT JOIN autores a ON l.autor_id = a.id
        WHERE e.usuario_id = ? AND e.status IN ('emprestado', 'atrasado')
        ORDER BY e.data_emprestimo DESC";
$emprestimos_ativos = dbFetchAll($sql, [$usuario_id]);

// Obter livros disponíveis
$sql = "SELECT l.id, l.titulo, l.descricao, l.imagem_capa, l.estoque_disponivel,
               a.nome as autor, c.nome as categoria
        FROM livros l
        LEFT JOIN autores a ON l.autor_id = a.id
        LEFT JOIN categorias c ON l.categoria_id = c.id
        WHERE l.ativo = TRUE AND l.estoque_disponivel > 0
        ORDER BY l.titulo";
$livros_disponiveis = dbFetchAll($sql);

// Se for admin, obter estatísticas gerais
$estatisticas_gerais = null;
if ($is_admin) {
    $estatisticas_gerais = getEstatisticasEmprestimos();
    
    // Calcular multas automaticamente
    $multas_calculadas = calcularMultasAutomaticas();
    
    // Obter empréstimos atrasados
    $sql = "SELECT e.id, u.nome as usuario, u.cpf, u.telefone, l.titulo,
                   e.data_emprestimo, e.data_devolucao_prevista,
                   DATEDIFF(CURRENT_DATE, e.data_devolucao_prevista) as dias_atraso
            FROM emprestimos e
            JOIN usuarios u ON e.usuario_id = u.id
            JOIN livros l ON e.livro_id = l.id
            WHERE e.status = 'atrasado'
            ORDER BY e.data_devolucao_prevista";
    $emprestimos_atrasados = dbFetchAll($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Biblioteca Arco-Íris</title>
    <link rel="icon" href="favicon.ico">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .book-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .book-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .book-info {
            padding: 15px;
        }
        .book-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .book-author {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9em;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .btn-danger {
            background: #e74c3c;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .status-emprestado { color: #28a745; }
        .status-atrasado { color: #dc3545; }
        .status-devolvido { color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Mensagens do sistema -->
        <?php echo showMessage(); ?>
        
        <!-- Header -->
        <div class="header">
            <div>
                <h1>Biblioteca Arco-Íris</h1>
                <p>Bem-vindo ao sistema de gerenciamento</p>
            </div>
            <div class="user-info">
                <div>
                    <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong><br>
                    <small><?php echo $is_admin ? 'Administrador' : 'Usuário'; ?></small>
                </div>
                <a href="logout.php" class="btn btn-danger">Sair</a>
            </div>
        </div>

        <!-- Estatísticas do Usuário -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $estatisticas['total_emprestimos']; ?></h3>
                <p>Total de Empréstimos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $estatisticas['emprestimos_ativos']; ?></h3>
                <p>Empréstimos Ativos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $estatisticas['total_devolvidos']; ?></h3>
                <p>Livros Devolvidos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $estatisticas['emprestimos_atrasados']; ?></h3>
                <p>Empréstimos Atrasados</p>
            </div>
        </div>

        <!-- Alertas -->
        <?php if ($usuario['tem_debito']): ?>
            <div class="alert alert-danger">
                ⚠️ Você possui multas pendentes. Regularize sua situação para continuar emprestando livros.
            </div>
        <?php endif; ?>

        <?php if ($usuario['tem_doacao_pendente']): ?>
            <div class="alert alert-warning">
                📦 Você tem uma doação de item de higiene pendente de aprovação.
            </div>
        <?php endif; ?>

        <!-- Empréstimos Ativos -->
        <div class="section">
            <h2>Meus Empréstimos Ativos</h2>
            <?php if (empty($emprestimos_ativos)): ?>
                <p>Você não possui empréstimos ativos.</p>
            <?php else: ?>
                <div class="book-grid">
                    <?php foreach ($emprestimos_ativos as $emprestimo): ?>
                        <div class="book-card">
                            <img src="<?php echo htmlspecialchars($emprestimo['imagem_capa']); ?>" 
                                 alt="<?php echo htmlspecialchars($emprestimo['titulo']); ?>">
                            <div class="book-info">
                                <div class="book-title"><?php echo htmlspecialchars($emprestimo['titulo']); ?></div>
                                <div class="book-author"><?php echo htmlspecialchars($emprestimo['autor']); ?></div>
                                <p><strong>Status:</strong> 
                                    <span class="status-<?php echo $emprestimo['status']; ?>">
                                        <?php echo ucfirst($emprestimo['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Devolução:</strong> <?php echo formatDate($emprestimo['data_devolucao_prevista']); ?></p>
                                <?php if ($emprestimo['status'] === 'emprestado' && !$emprestimo['renovado']): ?>
                                    <button class="btn" onclick="renovarEmprestimo(<?php echo $emprestimo['id']; ?>)">
                                        Renovar
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-danger" onclick="devolverLivro(<?php echo $emprestimo['id']; ?>)">
                                    Devolver
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Livros Disponíveis -->
        <div class="section">
            <h2>Livros Disponíveis</h2>
            <div class="book-grid">
                <?php foreach ($livros_disponiveis as $livro): ?>
                    <div class="book-card">
                        <img src="<?php echo htmlspecialchars($livro['imagem_capa']); ?>" 
                             alt="<?php echo htmlspecialchars($livro['titulo']); ?>">
                        <div class="book-info">
                            <div class="book-title"><?php echo htmlspecialchars($livro['titulo']); ?></div>
                            <div class="book-author"><?php echo htmlspecialchars($livro['autor']); ?></div>
                            <p><strong>Categoria:</strong> <?php echo htmlspecialchars($livro['categoria']); ?></p>
                            <p><strong>Disponível:</strong> <?php echo $livro['estoque_disponivel']; ?> exemplares</p>
                            <button class="btn" onclick="emprestarLivro(<?php echo $livro['id']; ?>)">
                                Emprestar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Seção Administrativa -->
        <?php if ($is_admin): ?>
            <div class="section">
                <h2>Painel Administrativo</h2>
                
                <!-- Estatísticas Gerais -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $estatisticas_gerais['total_emprestimos']; ?></h3>
                        <p>Total de Empréstimos</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $estatisticas_gerais['emprestados']; ?></h3>
                        <p>Livros Emprestados</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $estatisticas_gerais['atrasados']; ?></h3>
                        <p>Empréstimos Atrasados</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo formatMoney($estatisticas_gerais['total_multas']); ?></h3>
                        <p>Total em Multas</p>
                    </div>
                </div>

                <!-- Empréstimos Atrasados -->
                <?php if (!empty($emprestimos_atrasados)): ?>
                    <h3>Empréstimos Atrasados</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>CPF</th>
                                <th>Telefone</th>
                                <th>Livro</th>
                                <th>Data Empréstimo</th>
                                <th>Data Devolução</th>
                                <th>Dias Atraso</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emprestimos_atrasados as $atrasado): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($atrasado['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($atrasado['cpf']); ?></td>
                                    <td><?php echo htmlspecialchars($atrasado['telefone']); ?></td>
                                    <td><?php echo htmlspecialchars($atrasado['titulo']); ?></td>
                                    <td><?php echo formatDate($atrasado['data_emprestimo']); ?></td>
                                    <td><?php echo formatDate($atrasado['data_devolucao_prevista']); ?></td>
                                    <td><?php echo $atrasado['dias_atraso']; ?> dias</td>
                                    <td>
                                        <button class="btn" onclick="contatarUsuario(<?php echo $atrasado['id']; ?>)">
                                            Contatar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Links Administrativos -->
                <div style="margin-top: 20px;">
                    <a href="usuarios.php" class="btn">Gerenciar Usuários</a>
                    <a href="fornecedores.php" class="btn">Gerenciar Fornecedores</a>
                    <a href="agendamentos.php" class="btn">Gerenciar Agendamentos</a>
                    <a href="graficos.php" class="btn">Ver Gráficos</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Função para emprestar livro
        function emprestarLivro(livroId) {
            if (confirm('Deseja emprestar este livro?')) {
                const formData = new FormData();
                formData.append('acao', 'emprestar');
                formData.append('usuario_id', <?php echo $usuario_id; ?>);
                formData.append('livro_id', livroId);

                fetch('PHP/emprestimos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Livro emprestado com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao conectar com o servidor');
                });
            }
        }

        // Função para devolver livro
        function devolverLivro(emprestimoId) {
            if (confirm('Deseja devolver este livro?')) {
                const formData = new FormData();
                formData.append('acao', 'devolver');
                formData.append('emprestimo_id', emprestimoId);
                formData.append('usuario_id', <?php echo $usuario_id; ?>);

                fetch('PHP/emprestimos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Livro devolvido com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao conectar com o servidor');
                });
            }
        }

        // Função para renovar empréstimo
        function renovarEmprestimo(emprestimoId) {
            if (confirm('Deseja renovar este empréstimo?')) {
                const formData = new FormData();
                formData.append('acao', 'renovar');
                formData.append('emprestimo_id', emprestimoId);
                formData.append('usuario_id', <?php echo $usuario_id; ?>);

                fetch('PHP/emprestimos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Empréstimo renovado com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao conectar com o servidor');
                });
            }
        }

        // Função para contatar usuário (admin)
        function contatarUsuario(emprestimoId) {
            alert('Funcionalidade de contato será implementada aqui.');
        }
    </script>
</body>
</html>


