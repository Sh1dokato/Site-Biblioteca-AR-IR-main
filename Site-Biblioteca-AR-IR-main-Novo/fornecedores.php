<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores - Biblioteca Arco-Íris</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="CSS/fornecedores.css">
</head>
<body>
    <div>
        <a class="voltar" href="inicio-admin.php">Voltar</a>
    </div>
    
    <header class="header">
        <div class="header-title">
            <img src="IMG/logo.png" alt="Logo" style="height: 30px;">
            <span>Biblioteca Arco-Íris - Gestão de Fornecedores</span>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Gestão de Fornecedores</h1>
            <button class="add-supplier-btn" onclick="abrirModalAdicionar()">
                <span>+</span> Adicionar Fornecedor
            </button>
        </div>

        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <h3 id="totalFornecedores">0</h3>
                    <p>Total de Fornecedores</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3 id="totalDoacoes">0</h3>
                    <p>Total de Doações</p>
                </div>
            </div>
        </div>

        <div class="search-filter-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Pesquisar fornecedores...">
                <button class="search-btn">🔍</button>
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="todos">📋 Todos</button>
                <button class="filter-btn" data-filter="ativos">✅ Ativos</button>
                <button class="filter-btn" data-filter="com-doacoes">📚 Com Doações</button>
                <button class="filter-btn" data-filter="top-fornecedores">🏆 Top Fornecedores</button>
            </div>
        </div>

        <div class="suppliers-table-container">
            <div class="table-wrapper">
                <table id="suppliersTable">
                <thead>
                    <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF/CNPJ</th>
                            <th>Telefone</th>
                            <th>Doações</th>
                            <th>Status</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                    </tr>
                </thead>
                    <tbody id="suppliersTableBody">
                        <!-- Fornecedores serão inseridos aqui pelo JavaScript -->
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar/editar fornecedor -->
    <div id="supplierModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2 id="modalTitle">Adicionar Fornecedor</h2>
            <form id="supplierForm">
                <div class="form-group">
                    <label for="supplierName">Nome Completo:</label>
                    <input type="text" id="supplierName" required>
                </div>
                <div class="form-group">
                    <label for="supplierDocument">CPF/CNPJ:</label>
                    <input type="text" id="supplierDocument" maxlength="18" placeholder="000.000.000-00 ou 00.000.000/0000-00" required>
                </div>
                <div class="form-group">
                    <label for="supplierPhone">Telefone:</label>
                    <input type="tel" id="supplierPhone" maxlength="15" placeholder="(00) 00000-0000" required>
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

    <script>
        let fornecedores = [];
        let fornecedorEditando = null;
        let acaoConfirmacao = null;

        // Funções de mascaramento
        function aplicarMascaraCPFCNPJ(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length <= 11) {
                // CPF: 000.000.000-00
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                // CNPJ: 00.000.000/0000-00
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            
            input.value = value;
        }

        function aplicarMascaraTelefone(input) {
            let value = input.value.replace(/\D/g, '');
            
            if (value.length <= 10) {
                // Telefone fixo: (00) 0000-0000
                value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                // Celular: (00) 00000-0000
                value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            
            input.value = value;
        }

        // Função para gerar ID sequencial
        function gerarNovoId() {
            if (fornecedores.length === 0) {
                return 1;
            }
            const maxId = Math.max(...fornecedores.map(f => f.id));
            return maxId + 1;
        }

        // Inicializar dados
        function initializeData() {
            fornecedores = JSON.parse(localStorage.getItem('fornecedores') || '[]');
            
            // Se não houver fornecedores, criar alguns de exemplo
            if (fornecedores.length === 0) {
                const fornecedoresExemplo = [
                    {
                        id: 1,
                        name: "Editora Livros & Cia",
                        document: "12.345.678/0001-90",
                        phone: "(47) 3333-4444",
                        email: "contato@livroscia.com",
                        status: "ativo",
                        dataCadastro: "2025-01-15",
                        doacoes: 15
                    },

                ];
                
                localStorage.setItem('fornecedores', JSON.stringify(fornecedoresExemplo));
                fornecedores = fornecedoresExemplo;
            }
            
            updateStats();
            renderSuppliers();
        }

        // Atualizar estatísticas
        function updateStats() {
            const totalFornecedores = fornecedores.length;
            const totalDoacoes = fornecedores.reduce((sum, f) => sum + (f.doacoes || 0), 0);

            document.getElementById('totalFornecedores').textContent = totalFornecedores;
            document.getElementById('totalDoacoes').textContent = totalDoacoes;
        }

        // Renderizar tabela de fornecedores
        function renderSuppliers(filter = '') {
            const tbody = document.getElementById('suppliersTableBody');
            
            let fornecedoresFiltrados = fornecedores.filter(fornecedor => 
                fornecedor.name.toLowerCase().includes(filter.toLowerCase()) ||
                fornecedor.document.toLowerCase().includes(filter.toLowerCase()) ||
                fornecedor.phone.toLowerCase().includes(filter.toLowerCase())
            );

            tbody.innerHTML = fornecedoresFiltrados.map(fornecedor => {
                const dataCadastro = new Date(fornecedor.dataCadastro).toLocaleDateString('pt-BR');
                const statusClass = fornecedor.status === 'ativo' ? 'status-ativo' : 'status-inativo';
                
                return `
                    <tr>
                        <td>${fornecedor.id}</td>
                        <td>${fornecedor.name}</td>
                        <td>${fornecedor.document}</td>
                        <td>${fornecedor.phone}</td>
                        <td>${fornecedor.doacoes || 0}</td>
                        <td class="${statusClass}">${fornecedor.status}</td>
                        <td>${dataCadastro}</td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editarFornecedor(${fornecedor.id})" title="Editar">
                                ✏️
                            </button>
                            <button class="action-btn ${fornecedor.status === 'ativo' ? 'block' : 'unblock'}" 
                                    onclick="toggleStatus(${fornecedor.id})" 
                                    title="${fornecedor.status === 'ativo' ? 'Desativar' : 'Ativar'}">
                                ${fornecedor.status === 'ativo' ? '🚫' : '✅'}
                            </button>
                            <button class="action-btn delete" onclick="excluirFornecedor(${fornecedor.id})" title="Excluir">
                                🗑️
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Abrir modal para adicionar fornecedor
        function abrirModalAdicionar() {
            fornecedorEditando = null;
            document.getElementById('modalTitle').textContent = 'Adicionar Fornecedor';
            document.getElementById('supplierForm').reset();
            document.getElementById('supplierModal').style.display = 'block';
        }

        // Editar fornecedor
        function editarFornecedor(id) {
            fornecedorEditando = fornecedores.find(f => f.id === id);
            if (!fornecedorEditando) return;

            document.getElementById('modalTitle').textContent = 'Editar Fornecedor';
            document.getElementById('supplierName').value = fornecedorEditando.name;
            document.getElementById('supplierDocument').value = fornecedorEditando.document;
            document.getElementById('supplierPhone').value = fornecedorEditando.phone;
            document.getElementById('supplierModal').style.display = 'block';
        }

        // Fechar modal
        function fecharModal() {
            document.getElementById('supplierModal').style.display = 'none';
            fornecedorEditando = null;
        }

        // Salvar fornecedor
        document.getElementById('supplierForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const supplierData = {
                name: document.getElementById('supplierName').value,
                document: document.getElementById('supplierDocument').value,
                phone: document.getElementById('supplierPhone').value,
                email: fornecedorEditando ? fornecedorEditando.email : '',
                status: fornecedorEditando ? fornecedorEditando.status : 'ativo'
            };

            if (fornecedorEditando) {
                // Editar fornecedor existente
                Object.assign(fornecedorEditando, supplierData);
                } else {
                // Adicionar novo fornecedor
                const novoFornecedor = {
                    ...supplierData,
                    id: gerarNovoId(),
                    dataCadastro: new Date().toISOString().split('T')[0],
                    doacoes: 0
                };
                fornecedores.push(novoFornecedor);
            }

            localStorage.setItem('fornecedores', JSON.stringify(fornecedores));
            updateStats();
            renderSuppliers(document.getElementById('searchInput').value);
            fecharModal();
        });

        // Toggle status do fornecedor
        function toggleStatus(id) {
            const fornecedor = fornecedores.find(f => f.id === id);
            if (!fornecedor) return;

            const novaAcao = fornecedor.status === 'ativo' ? 'desativar' : 'ativar';
            const novaStatus = fornecedor.status === 'ativo' ? 'inativo' : 'ativo';
            
            mostrarConfirmacao(
                `Deseja ${novaAcao} o fornecedor "${fornecedor.name}"?`,
                () => {
                    fornecedor.status = novaStatus;
                    localStorage.setItem('fornecedores', JSON.stringify(fornecedores));
                    updateStats();
                    renderSuppliers(document.getElementById('searchInput').value);
                }
            );
        }

        // Excluir fornecedor
        function excluirFornecedor(id) {
            const fornecedor = fornecedores.find(f => f.id === id);
            if (!fornecedor) return;

            mostrarConfirmacao(
                `Deseja excluir o fornecedor "${fornecedor.name}"? Esta ação não pode ser desfeita.`,
                () => {
                    fornecedores = fornecedores.filter(f => f.id !== id);
                    localStorage.setItem('fornecedores', JSON.stringify(fornecedores));
                    updateStats();
                    renderSuppliers(document.getElementById('searchInput').value);
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
                let fornecedoresFiltrados = [...fornecedores];
                
                switch(filter) {
                    case 'ativos':
                        fornecedoresFiltrados = fornecedores.filter(f => f.status === 'ativo');
                        break;
                    case 'com-doacoes':
                        fornecedoresFiltrados = fornecedores.filter(f => (f.doacoes || 0) > 0);
                        break;
                    case 'top-fornecedores':
                        fornecedoresFiltrados = fornecedores
                            .filter(f => (f.doacoes || 0) > 0)
                            .sort((a, b) => (b.doacoes || 0) - (a.doacoes || 0))
                            .slice(0, 10);
                        break;
                }
                
                renderSuppliersFiltrados(fornecedoresFiltrados);
            });
        });

        function renderSuppliersFiltrados(fornecedoresFiltrados) {
            const tbody = document.getElementById('suppliersTableBody');
            
            tbody.innerHTML = fornecedoresFiltrados.map(fornecedor => {
                const dataCadastro = new Date(fornecedor.dataCadastro).toLocaleDateString('pt-BR');
                const statusClass = fornecedor.status === 'ativo' ? 'status-ativo' : 'status-inativo';
                
                return `
                    <tr>
                        <td>${fornecedor.id}</td>
                        <td>${fornecedor.name}</td>
                        <td>${fornecedor.document}</td>
                        <td>${fornecedor.phone}</td>
                        <td>${fornecedor.doacoes || 0}</td>
                        <td class="${statusClass}">${fornecedor.status}</td>
                        <td>${dataCadastro}</td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editarFornecedor(${fornecedor.id})" title="Editar">
                                ✏️
                            </button>
                            <button class="action-btn ${fornecedor.status === 'ativo' ? 'block' : 'unblock'}" 
                                    onclick="toggleStatus(${fornecedor.id})" 
                                    title="${fornecedor.status === 'ativo' ? 'Desativar' : 'Ativar'}">
                                ${fornecedor.status === 'ativo' ? '🚫' : '✅'}
                            </button>
                            <button class="action-btn delete" onclick="excluirFornecedor(${fornecedor.id})" title="Excluir">
                                🗑️
                            </button>
                            </td>
                        </tr>
                `;
            }).join('');
        }

        // Pesquisa
        document.getElementById('searchInput').addEventListener('input', function() {
            renderSuppliers(this.value);
        });

        // Adicionar funcionalidade ao botão de pesquisa
        document.querySelector('.search-btn').addEventListener('click', function() {
            const searchValue = document.getElementById('searchInput').value;
            renderSuppliers(searchValue);
        });

        // Fechar modais ao clicar fora
        window.onclick = function(event) {
            const supplierModal = document.getElementById('supplierModal');
            const confirmModal = document.getElementById('confirmModal');
            
            if (event.target === supplierModal) {
                fecharModal();
            }
            if (event.target === confirmModal) {
                fecharConfirmModal();
            }
        }

        // Adicionar event listeners para máscaras
        document.addEventListener('DOMContentLoaded', function() {
            const cpfCnpjInput = document.getElementById('supplierDocument');
            const telefoneInput = document.getElementById('supplierPhone');
            
            if (cpfCnpjInput) {
                cpfCnpjInput.addEventListener('input', function() {
                    aplicarMascaraCPFCNPJ(this);
                });
            }
            
            if (telefoneInput) {
                telefoneInput.addEventListener('input', function() {
                    aplicarMascaraTelefone(this);
                });
            }
        });

        // Inicializar quando a página carregar
        window.addEventListener('load', initializeData);
    </script>
</body>
</html> 