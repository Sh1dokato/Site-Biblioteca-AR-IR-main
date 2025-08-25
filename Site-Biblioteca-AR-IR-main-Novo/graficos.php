<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficos - Biblioteca Arco-Íris</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="CSS/graficos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div>
        <a class="voltar" href="inicio-admin.php">Voltar</a>
    </div>
    
    <header class="header">
        <div class="header-title">
            <img src="IMG/logo.png" alt="Logo" style="height: 30px;">
            <span>Biblioteca Arco-Íris - Análise de Dados</span>
        </div>
    </header>

    <div class="container">
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3 id="totalLivros">0</h3>
                    <p>Total de Livros</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-content">
                    <h3 id="livrosEmprestados">0</h3>
                    <p>Livros Emprestados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3 id="totalUsuarios">0</h3>
                    <p>Usuários Ativos</p>
                </div>
            </div>
        </div>
        
        <p id="taxaEmprestimo" style="display: none;">0%</p>
        <div class="charts-grid">
            <div class="chart-container">
                <h2>Livros Mais Emprestados</h2>
                <canvas id="livrosPopularesChart"></canvas>
            </div>
            
            <div class="chart-container">
                <h2>Empréstimos por Mês</h2>
                <canvas id="emprestimosMensaisChart"></canvas>
            </div>
        </div>

        <div class="data-tables">
            <div class="table-container">
                <h2>Top 10 Livros Mais Emprestados</h2>
                <div class="table-wrapper">
                    <table id="topLivrosTable">
                        <thead>
                            <tr>
                                <th>Posição</th>
                                <th>Livro</th>
                                <th>Autor</th>
                                <th>Empréstimos</th>
                                <th>Disponível</th>
                            </tr>
                        </thead>
                        <tbody id="topLivrosTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-container">
                <h2>Últimos Empréstimos</h2>
                <div class="table-wrapper">
                    <table id="ultimosEmprestimosTable">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Livro</th>
                                <th>Usuário</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="ultimosEmprestimosTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dados globais
        let livros = [];
        let emprestimos = [];
        let usuarios = [];

        // Inicializar dados
        function initializeData() {
            livros = JSON.parse(localStorage.getItem('livros') || '[]');
            emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
            usuarios = JSON.parse(localStorage.getItem('users') || '[]');
            
            // Se não houver empréstimos, criar alguns de exemplo
            if (emprestimos.length === 0 && livros.length > 0) {
                const empréstimosExemplo = [
                    {
                        id: 1,
                        titulo: "1984",
                        userId: 1,
                        dataEmprestimo: "2024-01-15",
                        status: "emprestado"
                    },
                    {
                        id: 2,
                        titulo: "O Pequeno Príncipe",
                        userId: 1,
                        dataEmprestimo: "2024-02-10",
                        status: "devolvido"
                    },
                    {
                        id: 3,
                        titulo: "1984",
                        userId: 1,
                        dataEmprestimo: "2024-02-20",
                        status: "emprestado"
                    },
                    {
                        id: 4,
                        titulo: "A Arte da Guerra",
                        userId: 1,
                        dataEmprestimo: "2024-01-05",
                        status: "devolvido"
                    },
                    {
                        id: 5,
                        titulo: "O Pequeno Príncipe",
                        userId: 1,
                        dataEmprestimo: "2024-03-01",
                        status: "emprestado"
                    }
                ];
                
                localStorage.setItem('emprestimos', JSON.stringify(empréstimosExemplo));
                emprestimos = empréstimosExemplo;
            }
            
            updateStats();
            updateCharts();
            updateTables();
        }

        // Atualizar estatísticas
        function updateStats() {
            const totalLivros = livros.length;
            const livrosEmprestados = emprestimos.filter(e => e.status === 'emprestado').length;
            const totalUsuarios = usuarios.length;
            const taxaEmprestimo = totalLivros > 0 ? Math.round((livrosEmprestados / totalLivros) * 100) : 0;

            document.getElementById('totalLivros').textContent = totalLivros;
            document.getElementById('livrosEmprestados').textContent = livrosEmprestados;
            document.getElementById('totalUsuarios').textContent = totalUsuarios;
            document.getElementById('taxaEmprestimo').textContent = taxaEmprestimo + '%';
        }

        // Atualizar gráficos
        function updateCharts() {
            updateLivrosPopularesChart();
            updateEmprestimosMensaisChart();
            updateCategoriasChart();
            updateStatusEmprestimosChart();
        }

        // Gráfico de livros mais populares
        function updateLivrosPopularesChart() {
            const ctx = document.getElementById('livrosPopularesChart').getContext('2d');
            
            // Contar empréstimos por livro
            const emprestimosPorLivro = {};
            emprestimos.forEach(emprestimo => {
                if (emprestimosPorLivro[emprestimo.titulo]) {
                    emprestimosPorLivro[emprestimo.titulo]++;
                } else {
                    emprestimosPorLivro[emprestimo.titulo] = 1;
                }
            });

            // Ordenar por quantidade de empréstimos
            const livrosOrdenados = Object.entries(emprestimosPorLivro)
                .sort(([,a], [,b]) => b - a)
                .slice(0, 5);

            const labels = livrosOrdenados.map(([titulo]) => titulo.length > 20 ? titulo.substring(0, 20) + '...' : titulo);
            const data = livrosOrdenados.map(([, count]) => count);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Empréstimos',
                        data: data,
                        backgroundColor: [
                            '#FF6B6B',
                            '#4ECDC4',
                            '#45B7D1',
                            '#96CEB4',
                            '#FFEAA7'
                        ],
                        borderColor: [
                            '#FF5252',
                            '#26A69A',
                            '#2196F3',
                            '#66BB6A',
                            '#FFC107'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de empréstimos mensais
        function updateEmprestimosMensaisChart() {
            const ctx = document.getElementById('emprestimosMensaisChart').getContext('2d');
            
            // Agrupar empréstimos por mês
            const emprestimosPorMes = {};
            emprestimos.forEach(emprestimo => {
                const data = new Date(emprestimo.dataEmprestimo);
                const mesAno = `${data.getMonth() + 1}/${data.getFullYear()}`;
                
                if (emprestimosPorMes[mesAno]) {
                    emprestimosPorMes[mesAno]++;
                } else {
                    emprestimosPorMes[mesAno] = 1;
                }
            });

            const labels = Object.keys(emprestimosPorMes).sort();
            const data = labels.map(mes => emprestimosPorMes[mes]);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Empréstimos',
                        data: data,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de categorias
        function updateCategoriasChart() {
            const ctx = document.getElementById('categoriasChart').getContext('2d');
            
            // Contar livros por categoria
            const livrosPorCategoria = {};
            livros.forEach(livro => {
                if (livrosPorCategoria[livro.categoria]) {
                    livrosPorCategoria[livro.categoria]++;
                } else {
                    livrosPorCategoria[livro.categoria] = 1;
                }
            });

            const labels = Object.keys(livrosPorCategoria);
            const data = Object.values(livrosPorCategoria);

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#FF6B6B',
                            '#4ECDC4',
                            '#45B7D1',
                            '#96CEB4',
                            '#FFEAA7',
                            '#DDA0DD',
                            '#98D8C8',
                            '#F7DC6F'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Gráfico de status dos empréstimos
        function updateStatusEmprestimosChart() {
            const ctx = document.getElementById('statusEmprestimosChart').getContext('2d');
            
            const emprestados = emprestimos.filter(e => e.status === 'emprestado').length;
            const devolvidos = emprestimos.filter(e => e.status === 'devolvido').length;
            const atrasados = emprestimos.filter(e => {
                if (e.status === 'emprestado') {
                    const dataEmprestimo = new Date(e.dataEmprestimo);
                    const hoje = new Date();
                    const diffTime = hoje - dataEmprestimo;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    return diffDays > 14; // Considera atrasado após 14 dias
                }
                return false;
            }).length;

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Emprestados', 'Devolvidos', 'Atrasados'],
                    datasets: [{
                        data: [emprestados, devolvidos, atrasados],
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#FF5722'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Atualizar tabelas
        function updateTables() {
            updateTopLivrosTable();
            updateUltimosEmprestimosTable();
        }

        // Tabela dos livros mais emprestados
        function updateTopLivrosTable() {
            const tbody = document.getElementById('topLivrosTableBody');
            
            // Contar empréstimos por livro
            const emprestimosPorLivro = {};
            emprestimos.forEach(emprestimo => {
                if (emprestimosPorLivro[emprestimo.titulo]) {
                    emprestimosPorLivro[emprestimo.titulo]++;
                } else {
                    emprestimosPorLivro[emprestimo.titulo] = 1;
                }
            });

            // Ordenar e pegar top 10
            const topLivros = Object.entries(emprestimosPorLivro)
                .sort(([,a], [,b]) => b - a)
                .slice(0, 10);

            tbody.innerHTML = topLivros.map(([titulo, count], index) => {
                const livro = livros.find(l => l.title === titulo);
                const autor = livro ? livro.autor : 'N/A';
                const disponivel = livro ? livro.estoque : 0;
                
                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${titulo}</td>
                        <td>${autor}</td>
                        <td>${count}</td>
                        <td>${disponivel}</td>
                    </tr>
                `;
            }).join('');
        }

        // Tabela dos últimos empréstimos
        function updateUltimosEmprestimosTable() {
            const tbody = document.getElementById('ultimosEmprestimosTableBody');
            
            // Ordenar empréstimos por data (mais recentes primeiro)
            const emprestimosOrdenados = emprestimos
                .sort((a, b) => new Date(b.dataEmprestimo) - new Date(a.dataEmprestimo))
                .slice(0, 10);

            tbody.innerHTML = emprestimosOrdenados.map(emprestimo => {
                const data = new Date(emprestimo.dataEmprestimo).toLocaleDateString('pt-BR');
                const usuario = usuarios.find(u => u.id === emprestimo.userId);
                const nomeUsuario = usuario ? usuario.name : 'Usuário não encontrado';
                
                let statusClass = '';
                let statusText = emprestimo.status;
                
                if (emprestimo.status === 'emprestado') {
                    const dataEmprestimo = new Date(emprestimo.dataEmprestimo);
                    const hoje = new Date();
                    const diffTime = hoje - dataEmprestimo;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays > 14) {
                        statusClass = 'status-atrasado';
                        statusText = 'Atrasado';
                    } else {
                        statusClass = 'status-emprestado';
                    }
                } else if (emprestimo.status === 'devolvido') {
                    statusClass = 'status-devolvido';
                }
                
                return `
                    <tr>
                        <td>${data}</td>
                        <td>${emprestimo.titulo}</td>
                        <td>${nomeUsuario}</td>
                        <td class="${statusClass}">${statusText}</td>
                    </tr>
                `;
            }).join('');
        }

        // Atualizar dados a cada 30 segundos
        setInterval(() => {
            initializeData();
        }, 30000);

        // Inicializar quando a página carregar
        window.addEventListener('load', initializeData);
    </script>
</body>
</html> 