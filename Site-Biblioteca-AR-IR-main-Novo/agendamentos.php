<?php
require_once 'conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - Biblioteca Arco-Íris</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="CSS/agendamentos.css">
</head>
<body>
    <div>
        <a class="voltar" href="inicio-admin.php">Voltar</a>
    </div>
    
    <header class="header">
        <div class="header-title">
            <img src="IMG/logo.png" alt="Logo" style="height: 30px;">
            <span>Biblioteca Arco-Íris - Gestão de Agendamentos</span>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Gestão de Agendamentos</h1>
            <div class="header-actions">
                <button class="btn-export" onclick="exportarAgendamentos()">
                    📊 Exportar Relatório
                </button>
                <button class="btn-refresh" onclick="carregarAgendamentos()">
                    🔄 Atualizar
                </button>
            </div>
        </div>

        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <h3 id="totalAgendamentos">0</h3>
                    <p>Total de Agendamentos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3 id="agendamentosHoje">0</h3>
                    <p>Agendamentos Hoje</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-content">
                    <h3 id="agendamentosPendentes">0</h3>
                    <p>Pendentes</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3 id="livrosMaisAgendados">0</h3>
                    <p>Livros Mais Agendados</p>
                </div>
            </div>
        </div>

        <div class="search-filter-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Pesquisar agendamentos...">
                <button class="search-btn">🔍</button>
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="todos">Todos</button>
                <button class="filter-btn" data-filter="hoje">Hoje</button>
                <button class="filter-btn" data-filter="pendentes">Pendentes</button>
                <button class="filter-btn" data-filter="concluidos">Concluídos</button>
            </div>
        </div>

        <div class="agendamentos-table-container">
            <div class="table-wrapper">
                <table id="agendamentosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Livro</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                            <th>Data Agendamento</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="agendamentosTableBody">
                        <!-- Agendamentos serão inseridos aqui pelo JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para detalhes do agendamento -->
    <div id="agendamentoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2 id="modalTitle">Detalhes do Agendamento</h2>
            <div class="agendamento-details">
                <div class="detail-row">
                    <label>Usuário:</label>
                    <span id="modalUsuario"></span>
                </div>
                <div class="detail-row">
                    <label>Livro:</label>
                    <span id="modalLivro"></span>
                </div>
                <div class="detail-row">
                    <label>Data:</label>
                    <span id="modalData"></span>
                </div>
                <div class="detail-row">
                    <label>Horário:</label>
                    <span id="modalHorario"></span>
                </div>
                <div class="detail-row">
                    <label>Status:</label>
                    <span id="modalStatus"></span>
                </div>
                <div class="detail-row">
                    <label>Data do Agendamento:</label>
                    <span id="modalDataAgendamento"></span>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-concluir" onclick="concluirAgendamento()">✅ Concluir</button>
                <button class="btn-cancelar" onclick="cancelarAgendamento()">❌ Cancelar</button>
                <button class="btn-fechar" onclick="fecharModal()">Fechar</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div id="confirmModal" class="modal">
        <div class="modal-content confirm-modal">
            <h3 id="confirmTitle">Confirmar Ação</h3>
            <p id="confirmMessage">Tem certeza que deseja realizar esta ação?</p>
            <div class="confirm-actions">
                <button class="btn-cancel" onclick="fecharConfirmModal()">Cancelar</button>
                <button class="btn-confirm" onclick="confirmarAcao()">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        let agendamentos = [];
        let agendamentoSelecionado = null;
        let acaoConfirmacao = null;

        // Inicializar dados
        function initializeData() {
            agendamentos = JSON.parse(localStorage.getItem('agendamentos') || '[]');
            updateStats();
            renderAgendamentos();
        }

        // Atualizar estatísticas
        function updateStats() {
            const hoje = new Date().toISOString().split('T')[0];
            const totalAgendamentos = agendamentos.length;
            const agendamentosHoje = agendamentos.filter(a => a.data === hoje).length;
            const agendamentosPendentes = agendamentos.filter(a => a.status === 'agendado').length;
            
            // Contar livros mais agendados
            const livrosAgendados = {};
            agendamentos.forEach(a => {
                if (livrosAgendados[a.livroTitulo]) {
                    livrosAgendados[a.livroTitulo]++;
                } else {
                    livrosAgendados[a.livroTitulo] = 1;
                }
            });
            const livrosMaisAgendados = Object.keys(livrosAgendados).length;

            document.getElementById('totalAgendamentos').textContent = totalAgendamentos;
            document.getElementById('agendamentosHoje').textContent = agendamentosHoje;
            document.getElementById('agendamentosPendentes').textContent = agendamentosPendentes;
            document.getElementById('livrosMaisAgendados').textContent = livrosMaisAgendados;
        }

        // Renderizar tabela de agendamentos
        function renderAgendamentos(filter = '') {
            const tbody = document.getElementById('agendamentosTableBody');
            
            let agendamentosFiltrados = agendamentos.filter(agendamento => 
                agendamento.userName.toLowerCase().includes(filter.toLowerCase()) ||
                agendamento.livroTitulo.toLowerCase().includes(filter.toLowerCase())
            );

            tbody.innerHTML = agendamentosFiltrados.map(agendamento => {
                const dataFormatada = new Date(agendamento.data).toLocaleDateString('pt-BR');
                const dataAgendamentoFormatada = new Date(agendamento.dataAgendamento).toLocaleDateString('pt-BR');
                const statusClass = agendamento.status === 'agendado' ? 'status-pendente' : 
                                  agendamento.status === 'concluido' ? 'status-concluido' : 'status-cancelado';
                
                return `
                    <tr>
                        <td>${agendamento.id}</td>
                        <td>${agendamento.userName}</td>
                        <td>${agendamento.livroTitulo}</td>
                        <td>${dataFormatada}</td>
                        <td>${agendamento.horario}</td>
                        <td class="${statusClass}">${agendamento.status}</td>
                        <td>${dataAgendamentoFormatada}</td>
                        <td class="actions">
                            <button class="action-btn view" onclick="verDetalhes(${agendamento.id})" title="Ver Detalhes">
                                👁️
                            </button>
                            <button class="action-btn ${agendamento.status === 'agendado' ? 'concluir' : 'disabled'}" 
                                    onclick="concluirAgendamento(${agendamento.id})" 
                                    title="Concluir"
                                    ${agendamento.status !== 'agendado' ? 'disabled' : ''}>
                                ✅
                            </button>
                            <button class="action-btn ${agendamento.status === 'agendado' ? 'cancelar' : 'disabled'}" 
                                    onclick="cancelarAgendamento(${agendamento.id})" 
                                    title="Cancelar"
                                    ${agendamento.status !== 'agendado' ? 'disabled' : ''}>
                                ❌
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Ver detalhes do agendamento
        function verDetalhes(id) {
            agendamentoSelecionado = agendamentos.find(a => a.id === id);
            if (!agendamentoSelecionado) return;

            document.getElementById('modalUsuario').textContent = agendamentoSelecionado.userName;
            document.getElementById('modalLivro').textContent = agendamentoSelecionado.livroTitulo;
            document.getElementById('modalData').textContent = new Date(agendamentoSelecionado.data).toLocaleDateString('pt-BR');
            document.getElementById('modalHorario').textContent = agendamentoSelecionado.horario;
            document.getElementById('modalStatus').textContent = agendamentoSelecionado.status;
            document.getElementById('modalDataAgendamento').textContent = new Date(agendamentoSelecionado.dataAgendamento).toLocaleDateString('pt-BR');

            document.getElementById('agendamentoModal').style.display = 'block';
        }

        // Concluir agendamento
        function concluirAgendamento(id) {
            const agendamento = agendamentos.find(a => a.id === id);
            if (!agendamento) return;

            mostrarConfirmacao(
                `Deseja marcar o agendamento de "${agendamento.livroTitulo}" como concluído?`,
                () => {
                    agendamento.status = 'concluido';
                    localStorage.setItem('agendamentos', JSON.stringify(agendamentos));
                    updateStats();
                    renderAgendamentos(document.getElementById('searchInput').value);
                }
            );
        }

        // Cancelar agendamento
        function cancelarAgendamento(id) {
            const agendamento = agendamentos.find(a => a.id === id);
            if (!agendamento) return;

            mostrarConfirmacao(
                `Deseja cancelar o agendamento de "${agendamento.livroTitulo}"?`,
                () => {
                    agendamento.status = 'cancelado';
                    localStorage.setItem('agendamentos', JSON.stringify(agendamentos));
                    updateStats();
                    renderAgendamentos(document.getElementById('searchInput').value);
                }
            );
        }

        // Fechar modal
        function fecharModal() {
            document.getElementById('agendamentoModal').style.display = 'none';
            agendamentoSelecionado = null;
        }

        // Mostrar modal de confirmação
        function mostrarConfirmacao(mensagem, acao) {
            document.getElementById('confirmMessage').textContent = mensagem;
            document.getElementById('confirmModal').style.display = 'block';
            acaoConfirmacao = acao;
        }

        // Fechar modal de confirmação
        function fecharConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            acaoConfirmacao = null;
        }

        // Confirmar ação
        function confirmarAcao() {
            if (acaoConfirmacao) {
                acaoConfirmacao();
            }
            fecharConfirmModal();
        }

        // Exportar relatório
        function exportarAgendamentos() {
            const hoje = new Date().toLocaleDateString('pt-BR');
            let relatorio = `Relatório de Agendamentos - ${hoje}\n\n`;
            
            agendamentos.forEach(agendamento => {
                relatorio += `ID: ${agendamento.id}\n`;
                relatorio += `Usuário: ${agendamento.userName}\n`;
                relatorio += `Livro: ${agendamento.livroTitulo}\n`;
                relatorio += `Data: ${new Date(agendamento.data).toLocaleDateString('pt-BR')}\n`;
                relatorio += `Horário: ${agendamento.horario}\n`;
                relatorio += `Status: ${agendamento.status}\n`;
                relatorio += `Data Agendamento: ${new Date(agendamento.dataAgendamento).toLocaleDateString('pt-BR')}\n`;
                relatorio += `----------------------------------------\n`;
            });

            const blob = new Blob([relatorio], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `relatorio_agendamentos_${hoje.replace(/\//g, '-')}.txt`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Carregar agendamentos
        function carregarAgendamentos() {
            initializeData();
        }

        // Filtros
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                let agendamentosFiltrados = [...agendamentos];
                
                switch(filter) {
                    case 'hoje':
                        const hoje = new Date().toISOString().split('T')[0];
                        agendamentosFiltrados = agendamentos.filter(a => a.data === hoje);
                        break;
                    case 'pendentes':
                        agendamentosFiltrados = agendamentos.filter(a => a.status === 'agendado');
                        break;
                    case 'concluidos':
                        agendamentosFiltrados = agendamentos.filter(a => a.status === 'concluido');
                        break;
                }
                
                renderAgendamentosFiltrados(agendamentosFiltrados);
            });
        });

        function renderAgendamentosFiltrados(agendamentosFiltrados) {
            const tbody = document.getElementById('agendamentosTableBody');
            
            tbody.innerHTML = agendamentosFiltrados.map(agendamento => {
                const dataFormatada = new Date(agendamento.data).toLocaleDateString('pt-BR');
                const dataAgendamentoFormatada = new Date(agendamento.dataAgendamento).toLocaleDateString('pt-BR');
                const statusClass = agendamento.status === 'agendado' ? 'status-pendente' : 
                                  agendamento.status === 'concluido' ? 'status-concluido' : 'status-cancelado';
                
                return `
                    <tr>
                        <td>${agendamento.id}</td>
                        <td>${agendamento.userName}</td>
                        <td>${agendamento.livroTitulo}</td>
                        <td>${dataFormatada}</td>
                        <td>${agendamento.horario}</td>
                        <td class="${statusClass}">${agendamento.status}</td>
                        <td>${dataAgendamentoFormatada}</td>
                        <td class="actions">
                            <button class="action-btn view" onclick="verDetalhes(${agendamento.id})" title="Ver Detalhes">
                                👁️
                            </button>
                            <button class="action-btn ${agendamento.status === 'agendado' ? 'concluir' : 'disabled'}" 
                                    onclick="concluirAgendamento(${agendamento.id})" 
                                    title="Concluir"
                                    ${agendamento.status !== 'agendado' ? 'disabled' : ''}>
                                ✅
                            </button>
                            <button class="action-btn ${agendamento.status === 'agendado' ? 'cancelar' : 'disabled'}" 
                                    onclick="cancelarAgendamento(${agendamento.id})" 
                                    title="Cancelar"
                                    ${agendamento.status !== 'agendado' ? 'disabled' : ''}>
                                ❌
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Pesquisa
        document.getElementById('searchInput').addEventListener('input', function() {
            renderAgendamentos(this.value);
        });

        // Fechar modais ao clicar fora
        window.onclick = function(event) {
            const agendamentoModal = document.getElementById('agendamentoModal');
            const confirmModal = document.getElementById('confirmModal');
            
            if (event.target === agendamentoModal) {
                fecharModal();
            }
            if (event.target === confirmModal) {
                fecharConfirmModal();
            }
        }

        // Inicializar quando a página carregar
        window.addEventListener('load', initializeData);
    </script>
</body>
</html> 