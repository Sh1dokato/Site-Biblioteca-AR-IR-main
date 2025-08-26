<?php
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Biblioteca Arco-Íris</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="CSS/usuarios.css">
</head>
<body>
    <div>
        <a class="voltar" href="inicio-admin.php">Voltar</a>
    </div>
    
    <header class="header">
        <div class="header-title">
            <img src="IMG/logo.png" alt="Logo" style="height: 30px;">
            <span>Biblioteca Arco-Íris - Gestão de Usuários</span>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Gestão de Usuários</h1>
            <button class="add-user-btn" onclick="abrirModalAdicionar()">
                <span>+</span> Adicionar Usuário
            </button>
        </div>

        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3 id="totalUsuarios">0</h3>
                    <p>Total de Usuários</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3 id="usuariosAtivos">0</h3>
                    <p>Usuários Ativos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <h3 id="usuariosBloqueados">0</h3>
                    <p>Usuários Bloqueados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3 id="mediaEmprestimos">0</h3>
                    <p>Média de Empréstimos</p>
                </div>
            </div>
        </div>

        <div class="search-filter-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Pesquisar usuários...">
                <button class="search-btn">🔍</button>
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="todos">Todos</button>
                <button class="filter-btn" data-filter="ativos">Ativos</button>
                <button class="filter-btn" data-filter="bloqueados">Bloqueados</button>
                <button class="filter-btn" data-filter="com-emprestimos">Com Empréstimos</button>
            </div>
        </div>

        <div class="users-table-container">
            <div class="table-wrapper">
                <table id="usersTable">
                <thead>
                    <tr>
                            <th>ID</th>
                            <th>Nome</th>
                        <th>Status</th>
                            <th>Empréstimos Ativos</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                        <!-- Usuários serão inseridos aqui pelo JavaScript -->
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar/editar usuário -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2 id="modalTitle">Adicionar Usuário</h2>
            <form id="userForm">
                <div class="form-group">
                    <label for="userName">Nome Completo:</label>
                    <input type="text" id="userName" required>
                </div>
                <div class="form-group">
                    <label for="userPassword">Senha:</label>
                    <input type="password" id="userPassword" required>
                </div>
                <div class="form-group">
                    <label for="userStatus">Status:</label>
                    <select id="userStatus">
                        <option value="ativo">Ativo</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-save">Salvar</button>
                </div>
            </form>
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

    <!-- Modal para gerenciar multas -->
    <div id="multaModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModalMulta()">&times;</span>
            <h2 id="multaModalTitle">Gerenciar Multas</h2>
            <div id="multaModalContent">
                <div class="user-info">
                    <h3 id="userNameMulta"></h3>
                </div>
                <div class="emprestimos-section">
                    <h4>Empréstimos Ativos</h4>
                    <div id="emprestimosList" class="emprestimos-list">
                        <!-- Lista de empréstimos será carregada aqui -->
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="fecharModalMulta()">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        let usuarios = [];
        let usuarioEditando = null;
        let acaoConfirmacao = null;

        // Inicializar dados
        function initializeData() {
            usuarios = JSON.parse(localStorage.getItem('users') || '[]');
            
            // Usuários serão criados através do sistema

            // Empréstimos serão criados através do sistema
            
            updateStats();
            renderUsers();
        }

        // Atualizar estatísticas
        function updateStats() {
            const totalUsuarios = usuarios.length;
            const usuariosAtivos = usuarios.filter(u => u.status === 'ativo').length;
            const usuariosBloqueados = usuarios.filter(u => u.status === 'bloqueado').length;
            
            // Calcular média de empréstimos
            const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
            const emprestimosAtivos = emprestimos.filter(e => e.status === 'emprestado').length;
            const mediaEmprestimos = totalUsuarios > 0 ? Math.round(emprestimosAtivos / totalUsuarios) : 0;

            document.getElementById('totalUsuarios').textContent = totalUsuarios;
            document.getElementById('usuariosAtivos').textContent = usuariosAtivos;
            document.getElementById('usuariosBloqueados').textContent = usuariosBloqueados;
            document.getElementById('mediaEmprestimos').textContent = mediaEmprestimos;
        }

        // Renderizar tabela de usuários
        function renderUsers(filter = '') {
            const tbody = document.getElementById('usersTableBody');
            const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
            
            let usuariosFiltrados = usuarios.filter(usuario => 
                usuario.name.toLowerCase().includes(filter.toLowerCase())
            );

            tbody.innerHTML = usuariosFiltrados.map(usuario => {
                const emprestimosAtivos = emprestimos.filter(e => 
                    e.userId === usuario.id && e.status === 'emprestado'
                ).length;
                
                const statusClass = usuario.status === 'ativo' ? 'status-ativo' : 'status-bloqueado';
                
                return `
                    <tr>
                        <td>${usuario.id}</td>
                        <td>${usuario.name}</td>
                        <td class="${statusClass}">${usuario.status}</td>
                        <td>${emprestimosAtivos}</td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editarUsuario(${usuario.id})" title="Editar">
                                ✏️
                            </button>
                            <button class="action-btn ${usuario.status === 'ativo' ? 'block' : 'unblock'}" 
                                    onclick="toggleStatus(${usuario.id})" 
                                    title="${usuario.status === 'ativo' ? 'Bloquear' : 'Desbloquear'}">
                                ${usuario.status === 'ativo' ? '🚫' : '✅'}
                            </button>
                            <button class="action-btn multa" onclick="gerenciarMultas(${usuario.id})" title="Gerenciar Multas">
                                💰
                            </button>
                            <button class="action-btn delete" onclick="excluirUsuario(${usuario.id})" title="Excluir">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Abrir modal para adicionar usuário
        function abrirModalAdicionar() {
            usuarioEditando = null;
            document.getElementById('modalTitle').textContent = 'Adicionar Usuário';
            document.getElementById('userForm').reset();
            document.getElementById('userModal').style.display = 'block';
        }

        // Editar usuário
        function editarUsuario(id) {
            usuarioEditando = usuarios.find(u => u.id === id);
            if (!usuarioEditando) return;

            document.getElementById('modalTitle').textContent = 'Editar Usuário';
            document.getElementById('userName').value = usuarioEditando.name;
            document.getElementById('userPassword').value = usuarioEditando.password;
            document.getElementById('userStatus').value = usuarioEditando.status;
            document.getElementById('userModal').style.display = 'block';
        }

        // Fechar modal
        function fecharModal() {
            document.getElementById('userModal').style.display = 'none';
            usuarioEditando = null;
        }

        // Salvar usuário
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userData = {
                name: document.getElementById('userName').value,
                password: document.getElementById('userPassword').value,
                status: document.getElementById('userStatus').value
            };

            if (usuarioEditando) {
                // Editar usuário existente
                Object.assign(usuarioEditando, userData);
                } else {
                // Adicionar novo usuário
                const novoUsuario = {
                    ...userData,
                    id: Date.now(),
                    dataCadastro: new Date().toISOString().split('T')[0],
                    emprestimosAtivos: 0
                };
                usuarios.push(novoUsuario);
            }

            localStorage.setItem('users', JSON.stringify(usuarios));
            updateStats();
            renderUsers(document.getElementById('searchInput').value);
            fecharModal();
        });

        // Toggle status do usuário
        function toggleStatus(id) {
            const usuario = usuarios.find(u => u.id === id);
            if (!usuario) return;

            const novaAcao = usuario.status === 'ativo' ? 'bloquear' : 'desbloquear';
            const novaStatus = usuario.status === 'ativo' ? 'bloqueado' : 'ativo';
            
            mostrarConfirmacao(
                `Deseja ${novaAcao} o usuário "${usuario.name}"?`,
                () => {
                    usuario.status = novaStatus;
                    localStorage.setItem('users', JSON.stringify(usuarios));
                    updateStats();
                    renderUsers(document.getElementById('searchInput').value);
                }
            );
        }

        // Excluir usuário
        function excluirUsuario(id) {
            const usuario = usuarios.find(u => u.id === id);
            if (!usuario) return;

            mostrarConfirmacao(
                `Deseja excluir o usuário "${usuario.name}"? Esta ação não pode ser desfeita.`,
                () => {
                    usuarios = usuarios.filter(u => u.id !== id);
                    localStorage.setItem('users', JSON.stringify(usuarios));
                    updateStats();
                    renderUsers(document.getElementById('searchInput').value);
                }
            );
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

        // Filtros
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                let usuariosFiltrados = [...usuarios];
                
                switch(filter) {
                    case 'ativos':
                        usuariosFiltrados = usuarios.filter(u => u.status === 'ativo');
                        break;
                    case 'bloqueados':
                        usuariosFiltrados = usuarios.filter(u => u.status === 'bloqueado');
                        break;
                    case 'com-emprestimos':
                        const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
                        const usuariosComEmprestimos = new Set(
                            emprestimos.filter(e => e.status === 'emprestado').map(e => e.userId)
                        );
                        usuariosFiltrados = usuarios.filter(u => usuariosComEmprestimos.has(u.id));
                        break;
                }
                
                renderUsersFiltrados(usuariosFiltrados);
            });
        });

        function renderUsersFiltrados(usuariosFiltrados) {
            const tbody = document.getElementById('usersTableBody');
            const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
            
            tbody.innerHTML = usuariosFiltrados.map(usuario => {
                const emprestimosAtivos = emprestimos.filter(e => 
                    e.userId === usuario.id && e.status === 'emprestado'
                ).length;
                
                const statusClass = usuario.status === 'ativo' ? 'status-ativo' : 'status-bloqueado';
                
                return `
                    <tr>
                        <td>${usuario.id}</td>
                        <td>${usuario.name}</td>
                        <td class="${statusClass}">${usuario.status}</td>
                        <td>${emprestimosAtivos}</td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editarUsuario(${usuario.id})" title="Editar">
                                ✏️
                            </button>
                            <button class="action-btn ${usuario.status === 'ativo' ? 'block' : 'unblock'}" 
                                    onclick="toggleStatus(${usuario.id})" 
                                    title="${usuario.status === 'ativo' ? 'Bloquear' : 'Desbloquear'}">
                                ${usuario.status === 'ativo' ? '🚫' : '✅'}
                            </button>
                            <button class="action-btn multa" onclick="gerenciarMultas(${usuario.id})" title="Gerenciar Multas">
                                💰
                            </button>
                            <button class="action-btn delete" onclick="excluirUsuario(${usuario.id})" title="Excluir">
                                🗑️
                            </button>
                            </td>
                        </tr>
                `;
            }).join('');
        }

        // Pesquisa
        document.getElementById('searchInput').addEventListener('input', function() {
            renderUsers(this.value);
        });

        // Fechar modais ao clicar fora
        window.onclick = function(event) {
            const userModal = document.getElementById('userModal');
            const confirmModal = document.getElementById('confirmModal');
            const multaModal = document.getElementById('multaModal');
            
            if (event.target === userModal) {
                fecharModal();
            }
            if (event.target === confirmModal) {
                fecharConfirmModal();
            }
            if (event.target === multaModal) {
                fecharModalMulta();
            }
        }

        // Função para gerenciar multas
        function gerenciarMultas(userId) {
            console.log('Gerenciando multas para usuário ID:', userId);
            
            const usuario = usuarios.find(u => u.id === userId);
            if (!usuario) {
                console.log('Usuário não encontrado');
                return;
            }
            console.log('Usuário encontrado:', usuario);

            const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
            console.log('Empréstimos carregados:', emprestimos);
            
            const emprestimosAtivos = emprestimos.filter(e => 
                e.userId === userId && e.status === 'emprestado'
            );
            console.log('Empréstimos ativos:', emprestimosAtivos);

            // Preencher informações do usuário
            document.getElementById('userNameMulta').textContent = usuario.name;

            // Renderizar lista de empréstimos
            const emprestimosList = document.getElementById('emprestimosList');
            
            if (emprestimosAtivos.length === 0) {
                emprestimosList.innerHTML = '<p class="no-loans">Este usuário não possui empréstimos ativos.</p>';
            } else {
                emprestimosList.innerHTML = emprestimosAtivos.map(emprestimo => {
                    const dataEmprestimo = new Date(emprestimo.loanDate || emprestimo.dataEmprestimo).toLocaleDateString('pt-BR');
                    const dataDevolucao = new Date(emprestimo.dueDate || emprestimo.dataDevolucao).toLocaleDateString('pt-BR');
                    const multaAtual = emprestimo.debtAmount || 0;
                    const multaClass = multaAtual > 0 ? 'com-multa' : 'sem-multa';
                    
                    return `
                        <div class="emprestimo-item ${multaClass}">
                            <div class="emprestimo-info">
                                <h5>${emprestimo.bookTitle || emprestimo.titulo}</h5>
                                <p>Data do Empréstimo: ${dataEmprestimo}</p>
                                <p>Data de Devolução: ${dataDevolucao}</p>
                                <p class="multa-status">Multa: R$ ${multaAtual.toFixed(2)}</p>
                            </div>
                            <div class="multa-actions">
                                <button class="btn-adicionar-multa" onclick="adicionarMulta(${emprestimo.id})">
                                    ${multaAtual > 0 ? 'Alterar Multa' : 'Adicionar Multa'}
                                </button>
                                ${multaAtual > 0 ? `<button class="btn-remover-multa" onclick="removerMulta(${emprestimo.id})">Remover Multa</button>` : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            }

            console.log('Exibindo modal de multas');
            document.getElementById('multaModal').style.display = 'block';
        }

        // Função para adicionar/alterar multa
        function adicionarMulta(emprestimoId) {
            console.log('Adicionando multa para empréstimo ID:', emprestimoId);
            
            const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
            const emprestimo = emprestimos.find(e => e.id === emprestimoId);
            
            if (!emprestimo) {
                console.log('Empréstimo não encontrado');
                return;
            }
            console.log('Empréstimo encontrado:', emprestimo);

            const valorAtual = emprestimo.debtAmount || 0;
            const novoValor = prompt(`Digite o valor da multa (atual: R$ ${valorAtual.toFixed(2)}):`, valorAtual.toFixed(2));
            
            if (novoValor !== null && !isNaN(parseFloat(novoValor))) {
                emprestimo.debtAmount = parseFloat(novoValor);
                localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
                console.log('Multa atualizada:', emprestimo.debtAmount);
                
                // Atualizar a lista de empréstimos no modal
                const userId = emprestimo.userId;
                gerenciarMultas(userId);
            }
        }

        // Função para remover multa
        function removerMulta(emprestimoId) {
            console.log('Removendo multa para empréstimo ID:', emprestimoId);
            
            const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
            const emprestimo = emprestimos.find(e => e.id === emprestimoId);
            
            if (!emprestimo) {
                console.log('Empréstimo não encontrado');
                return;
            }
            console.log('Empréstimo encontrado:', emprestimo);

            if (confirm('Deseja remover a multa deste empréstimo?')) {
                emprestimo.debtAmount = 0;
                localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
                console.log('Multa removida');
                
                // Atualizar a lista de empréstimos no modal
                const userId = emprestimo.userId;
                gerenciarMultas(userId);
            }
        }

        // Função para fechar modal de multas
        function fecharModalMulta() {
            document.getElementById('multaModal').style.display = 'none';
        }

        // Inicializar quando a página carregar
        window.addEventListener('load', initializeData);
    </script>
</body>
</html> 