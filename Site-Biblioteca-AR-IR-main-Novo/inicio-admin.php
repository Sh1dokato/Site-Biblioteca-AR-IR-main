<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca Arco-Íris - Administração</title>
    <link rel="icon" href="favicon.ico">
    <link rel="stylesheet" href="CSS/inicioadmin.css">
</head>
<body>
    <div>
        <a class="voltar" href="index.php">Voltar</a>
      </div>
    <header class="header">
        <div>
            <a class="voltar" href="index.php">Voltar</a>
          </div>
        <div class="header-title">
            <img src="IMG/logo.png" alt="Logo" style="height: 30px;">
            <span>Biblioteca Arco-Íris - Painel Administrativo</span>
        </div>
        <div class="header-buttons">
            <div class="dropdown-menu">
                <a href="fornecedores.php" class="header-btn dropdown-trigger">Fornecedores ▼</a>
                <div class="dropdown-content">
                    <a href="fornecedores.php" class="dropdown-item">👥 Ver Fornecedores</a>
                    <a href="cadastrar-fornecedores.php" class="dropdown-item">➕ Cadastrar Fornecedor</a>
                </div>
            </div>
            
            <a href="graficos.php" style="text-decoration: none;">
                <button class="graficos">
                    <span>Gráficos</span>
                </button>
            </a>
            
            <div class="dropdown-menu">
                <a href="usuarios.php" class="header-btn dropdown-trigger">Usuários ▼</a>
                <div class="dropdown-content">
                    <a href="usuarios.php" class="dropdown-item">👥 Gerenciar Usuários</a>
                    <a href="agendamentos.php" class="dropdown-item">📅 Agendamentos</a>
                    <button class="dropdown-item-btn" onclick="toggleDonationsPanel()">
                        <span>🎁 Doações Pendentes</span>
                        <span class="count-badge" id="donationsCount">0</span>
                    </button>
                    <button class="dropdown-item-btn" onclick="toggleDevolucoesPanel()">
                        <span>📚 Devoluções Pendentes</span>
                        <span class="count-badge" id="devolucoesCount">0</span>
                    </button>
                </div>
            </div>
            <a href="index.php" class="header-btn">Sair</a>
        </div>
    </header>

    <div class="admin-container">
        <div class="books-section">
            <div class="books-header">
                <h2 class="books-title">Livros Disponíveis</h2>
                <button class="action-btn" onclick="showAddBookModal()">+ Adicionar Novo Livro</button>
            </div>
            <div class="books-grid" id="booksGrid">
                <!-- Os livros serão inseridos aqui via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal para adicionar/editar livro -->
    <div id="bookModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Adicionar Novo Livro</h2>
            <form id="bookForm" class="modal-form">
                <input type="hidden" id="bookId">
                <input type="text" id="bookTitle" placeholder="Título do livro" required>
                <input type="text" id="bookImage" placeholder="URL da imagem" required>
                <input type="number" id="bookEstoque" placeholder="Quantidade em estoque" min="0" required>
                <input type="text" id="bookAutor" placeholder="Autor do livro" required>
                <input type="number" id="bookAno" placeholder="Ano de publicação" min="1000" max="2024" required>
                <input type="number" id="bookPaginas" placeholder="Número de páginas" min="1" required>
                <input type="text" id="bookCategoria" placeholder="Categoria do livro" required>
                <textarea id="bookDescription" placeholder="Sinopse do livro" rows="4" required></textarea>
                <button type="submit">Salvar</button>
            </form>
        </div>
    </div>

    <div class="donations-panel" id="donationsPanel">
        <div class="donations-header">
            <h2 class="donations-title">Doações Pendentes</h2>
            <button class="close-donations" onclick="toggleDonationsPanel()">&times;</button>
        </div>
        <div id="donationsList">
            <!-- As doações pendentes serão inseridas aqui via JavaScript -->
        </div>
    </div>

    <div class="devolucoes-panel" id="devolucoesPanel">
        <div class="devolucoes-header">
            <h2 class="devolucoes-title">Devoluções Pendentes</h2>
            <button class="close-devolucoes" onclick="toggleDevolucoesPanel()">&times;</button>
        </div>
        <div id="devolucoesList">
            <!-- As devoluções pendentes serão inseridas aqui via JavaScript -->
        </div>
    </div>

    <script>
        // Verificar se o usuário está logado e é admin
        window.addEventListener('load', function() {
            const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
            if (!usuarioLogado || !usuarioLogado.isAdmin) {
                window.location.href = 'login.php';
                return;
            }
        });

        // Função para fazer logout
        document.querySelector('a[href="index.php"]').addEventListener('click', function(e) {
            e.preventDefault();
            localStorage.removeItem('usuarioLogado');
            window.location.href = 'index.php';
        });

        // Lista de livros
        const books = JSON.parse(localStorage.getItem('livros') || '[]');

        // Função para atualizar a grade de livros
        function updateBooksGrid() {
            const grid = document.getElementById('booksGrid');
            grid.innerHTML = books.map(book => `
                <div class="book-card">
                    <img src="${book.image}" alt="${book.title}" class="book-thumb">
                    <div class="book-title">${book.title}</div>
                    <div class="book-actions">
                        <button class="book-btn btn-edit" onclick="editBook(${book.id})">Editar</button>
                        <button class="book-btn btn-delete" onclick="deleteBook(${book.id})">Excluir</button>
                    </div>
                </div>
            `).join('');
        }

        // Funções do modal
        function showAddBookModal() {
            document.getElementById('modalTitle').textContent = 'Adicionar Novo Livro';
            document.getElementById('bookForm').reset();
            document.getElementById('bookId').value = '';
            document.getElementById('bookModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('bookModal').style.display = 'none';
        }

        // Função para editar livro
        function editBook(id) {
            const book = books.find(b => b.id === id);
            if (book) {
                document.getElementById('modalTitle').textContent = 'Editar Livro';
                document.getElementById('bookId').value = book.id;
                document.getElementById('bookTitle').value = book.title;
                document.getElementById('bookImage').value = book.image;
                document.getElementById('bookEstoque').value = book.estoque;
                document.getElementById('bookAutor').value = book.autor;
                document.getElementById('bookAno').value = book.ano;
                document.getElementById('bookPaginas').value = book.paginas;
                document.getElementById('bookCategoria').value = book.categoria;
                document.getElementById('bookDescription').value = book.descricao;
                document.getElementById('bookModal').style.display = 'block';
            }
        }

        // Função para excluir livro
        function deleteBook(id) {
            if (confirm('Tem certeza que deseja excluir este livro?')) {
                const index = books.findIndex(b => b.id === id);
                if (index !== -1) {
                    books.splice(index, 1);
                    localStorage.setItem('livros', JSON.stringify(books));
                    updateBooksGrid();
                }
            }
        }

        // Event listener para o formulário do livro
        document.getElementById('bookForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('bookId').value;
            const title = document.getElementById('bookTitle').value;
            const image = document.getElementById('bookImage').value;
            const estoque = parseInt(document.getElementById('bookEstoque').value);
            const autor = document.getElementById('bookAutor').value;
            const ano = parseInt(document.getElementById('bookAno').value);
            const paginas = parseInt(document.getElementById('bookPaginas').value);
            const categoria = document.getElementById('bookCategoria').value;
            const descricao = document.getElementById('bookDescription').value;

            if (id) {
                // Editando livro existente
                const index = books.findIndex(b => b.id === parseInt(id));
                if (index !== -1) {
                    books[index] = {
                        ...books[index],
                        title,
                        image,
                        estoque,
                        autor,
                        ano,
                        paginas,
                        categoria,
                        descricao
                    };
                }
            } else {
                // Adicionando novo livro
                const newId = books.length > 0 ? Math.max(...books.map(b => b.id)) + 1 : 1;
                books.push({ 
                    id: newId, 
                    title, 
                    image, 
                    estoque,
                    autor,
                    ano,
                    paginas,
                    categoria,
                    descricao
                });
            }

            // Salvar no localStorage
            localStorage.setItem('livros', JSON.stringify(books));
            updateBooksGrid();
            closeModal();
        });

        // Função para atualizar a lista de doações
        function updateDonationsList() {
            const donationsList = document.getElementById('donationsList');
            const pendingDonations = JSON.parse(localStorage.getItem('pendingDonations') || '[]');
            const donationsCount = document.getElementById('donationsCount');

            // Atualiza o contador
            donationsCount.textContent = pendingDonations.length;

            if (pendingDonations.length === 0) {
                donationsList.innerHTML = '<div class="empty-donations">Nenhuma doação pendente</div>';
                return;
            }

            donationsList.innerHTML = pendingDonations.map(donation => `
                <div class="donation-card" data-id="${donation.id}">
                    <div class="donation-info">
                        <strong>Usuário:</strong> ${donation.userName || donation.user}<br>
                        <strong>Livro com Multa:</strong> ${donation.bookTitle || donation.book}<br>
                        <strong>Valor da Multa:</strong> R$ ${donation.debtAmount ? donation.debtAmount.toFixed(2) : '0.00'}<br>
                        <strong>Doação:</strong> Item de Higiene<br>
                        <strong>Data:</strong> ${donation.date}
                    </div>
                    <div class="donation-actions">
                        <button class="donation-btn btn-confirm" data-id="${donation.id}">Confirmar</button>
                        <button class="donation-btn btn-reject" data-id="${donation.id}">Rejeitar</button>
                    </div>
                </div>
            `).join('');

            // Adicionar event listeners aos botões
            donationsList.querySelectorAll('.donation-btn.btn-confirm').forEach(button => {
                button.addEventListener('click', function() {
                    const donationId = this.dataset.id;
                    confirmDonation(parseInt(donationId));
                });
            });

            donationsList.querySelectorAll('.donation-btn.btn-reject').forEach(button => {
                 button.addEventListener('click', function() {
                    const donationId = this.dataset.id;
                    rejectDonation(parseInt(donationId));
                });
            });
        }

        function confirmDonation(id) {
            const pendingDonations = JSON.parse(localStorage.getItem('pendingDonations') || '[]');
            const donationIndex = pendingDonations.findIndex(d => d.id === id);
            
            if (donationIndex !== -1) {
                const donation = pendingDonations[donationIndex];
                
                // Marcar o empréstimo como devolvido e remover a multa
                const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
                
                const emprestimoIndex = emprestimos.findIndex(e => {
                    const matchUserId = e.userId === donation.userId;
                    const matchTitle = e.bookTitle === donation.bookTitle || e.titulo === donation.bookTitle;
                    const notReturned = !e.returned && e.status !== 'devolvido';
                    return matchUserId && matchTitle && notReturned;
                });
                
                if (emprestimoIndex !== -1) {
                    // Apenas remover a multa, manter o livro com o usuário
                    emprestimos[emprestimoIndex].debtAmount = 0;
                    localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
                }
                
                // Remover a doação da lista de pendentes
                pendingDonations.splice(donationIndex, 1);
                localStorage.setItem('pendingDonations', JSON.stringify(pendingDonations));
                
                // Atualizar status do usuário
                const users = JSON.parse(localStorage.getItem('users') || '[]');
                const userIndex = users.findIndex(u => u.id === donation.userId);
                if (userIndex !== -1) {
                    users[userIndex].hasPendingDonation = false;
                    localStorage.setItem('users', JSON.stringify(users));
                }
                
                // Atualizar usuário logado se for o mesmo usuário
                const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
                if (usuarioLogado && usuarioLogado.id === donation.userId) {
                    usuarioLogado.hasPendingDonation = false;
                    localStorage.setItem('usuarioLogado', JSON.stringify(usuarioLogado));
                }
                
                updateDonationsList();
                
                // Disparar evento para notificar outras páginas sobre a mudança
                window.dispatchEvent(new CustomEvent('doacaoConfirmada', {
                    detail: { userId: donation.userId, bookTitle: donation.bookTitle }
                }));
                
                alert('Doação de item de higiene confirmada com sucesso! A multa foi removida e o usuário mantém o livro.');
            }
        }

        function rejectDonation(id) {
            const pendingDonations = JSON.parse(localStorage.getItem('pendingDonations') || '[]');
            const donationIndex = pendingDonations.findIndex(d => d.id === id);
            
            if (donationIndex !== -1) {
                const donation = pendingDonations[donationIndex];
                
                // Remover a doação da lista de pendentes
                pendingDonations.splice(donationIndex, 1);
                localStorage.setItem('pendingDonations', JSON.stringify(pendingDonations));
                
                // Atualizar status do usuário
                const users = JSON.parse(localStorage.getItem('users') || '[]');
                const userIndex = users.findIndex(u => u.id === donation.userId);
                if (userIndex !== -1) {
                    users[userIndex].hasPendingDonation = false;
                    localStorage.setItem('users', JSON.stringify(users));
                }
                
                // Atualizar usuário logado se for o mesmo usuário
                const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
                if (usuarioLogado && usuarioLogado.id === donation.userId) {
                    usuarioLogado.hasPendingDonation = false;
                    localStorage.setItem('usuarioLogado', JSON.stringify(usuarioLogado));
                }
                
                updateDonationsList();
                alert('Doação de item de higiene rejeitada. O usuário ainda possui a multa pendente.');
            }
        }

        // Função para alternar o painel de doações
        function toggleDonationsPanel() {
            const panel = document.getElementById('donationsPanel');
            panel.classList.toggle('active');
        }

        // Fechar o painel ao clicar fora dele
        document.addEventListener('click', function(event) {
            const panel = document.getElementById('donationsPanel');
            const donationsBtn = document.querySelector('.donations-btn');
            
            if (!panel.contains(event.target) && !donationsBtn.contains(event.target) && panel.classList.contains('active')) {
                panel.classList.remove('active');
            }
        });

        // Função para formatar telefone
        function formatPhone(phone) {
            if (!phone) return 'Não informado';
            // Remove todos os caracteres não numéricos
            const numbers = phone.replace(/\D/g, '');
            // Formata como (XX) XXXXX-XXXX
            return `(${numbers.slice(0, 2)}) ${numbers.slice(2, 7)}-${numbers.slice(7, 11)}`;
        }

        // Função para atualizar a lista de devoluções
        function updateDevolucoesList() {
            try {
                const devolucoesList = document.getElementById('devolucoesList');
                const devolucoesCount = document.getElementById('devolucoesCount');
                
                // Obter solicitações de devolução
                let solicitacoesDevolucao = [];
                try {
                    const solicitacoesSalvas = localStorage.getItem('solicitacoesDevolucao');
                    if (solicitacoesSalvas) {
                        solicitacoesDevolucao = JSON.parse(solicitacoesSalvas);
                    }
                } catch (error) {
                    console.error('Erro ao ler solicitações de devolução:', error);
                }

                // Atualizar contador
                devolucoesCount.textContent = solicitacoesDevolucao.length;

                // Atualizar lista
                if (solicitacoesDevolucao.length === 0) {
                    devolucoesList.innerHTML = '<div class="empty-devolucoes">Nenhuma devolução pendente</div>';
                    return;
                }

                // Obter lista de livros e usuários
                const livros = JSON.parse(localStorage.getItem('livros') || '[]');
                const users = JSON.parse(localStorage.getItem('users') || '[]');

                devolucoesList.innerHTML = solicitacoesDevolucao.map(solicitacao => {
                    try {
                        // Encontrar o livro e o usuário correspondentes
                    const livro = livros.find(l => l.title === solicitacao.titulo);
                        const user = users.find(u => u.id === solicitacao.userId);
                        
                        // Garantir que temos valores válidos para todos os campos
                    const imagemLivro = livro ? livro.image : (solicitacao.imagem || 'IMG/default-book.jpg');
                        const nomeUsuario = user ? user.name : (solicitacao.userName || 'Usuário não encontrado');
                        const telefoneUsuario = user ? user.phone : (solicitacao.userPhone || '');
                        const dataEmprestimo = solicitacao.dataEmprestimo ? new Date(solicitacao.dataEmprestimo).toLocaleDateString('pt-BR') : 'Data não informada';

                    return `
                        <div class="devolucao-card" data-id="${solicitacao.id}">
                            <img src="${imagemLivro}" alt="${solicitacao.titulo}" class="book-thumb">
                            <div class="devolucao-info">
                                <div class="devolucao-title">${solicitacao.titulo}</div>
                                <div class="devolucao-user">${nomeUsuario}</div>
                            </div>
                            <div class="devolucao-actions">
                                <button class="devolucao-btn btn-confirm" onclick="confirmarDevolucao(${solicitacao.id})">Confirmar</button>
                                <button class="devolucao-btn btn-reject" onclick="rejeitarDevolucao(${solicitacao.id})">Rejeitar</button>
                            </div>
                        </div>
                    `;
                    } catch (error) {
                        console.error('Erro ao processar solicitação:', error);
                        return ''; // Retorna string vazia para não quebrar o layout
                    }
                }).join('');
                

            } catch (error) {
                console.error('Erro ao atualizar lista de devoluções:', error);
                devolucoesList.innerHTML = '<div class="empty-devolucoes">Erro ao carregar devoluções</div>';
            }
        }

        function confirmarDevolucao(id) {
            try {
                // Obter dados necessários
                const solicitacoesDevolucao = JSON.parse(localStorage.getItem('solicitacoesDevolucao') || '[]');
                const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
                const livros = JSON.parse(localStorage.getItem('livros') || '[]');
                
                const solicitacao = solicitacoesDevolucao.find(s => s.id === id);
                if (!solicitacao) {
                    throw new Error('Solicitação não encontrada');
                }

                // Atualizar status do empréstimo
                const emprestimo = emprestimos.find(e => e.id === solicitacao.emprestimoId);
                if (emprestimo) {
                    emprestimo.status = 'devolvido';
                    localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
                }

                // Atualizar estoque do livro
                const livro = livros.find(l => l.title === solicitacao.titulo);
                if (livro) {
                    livro.estoque++;
                    localStorage.setItem('livros', JSON.stringify(livros));
                }

                // Remover solicitação
                const solicitacoesAtualizadas = solicitacoesDevolucao.filter(s => s.id !== id);
                localStorage.setItem('solicitacoesDevolucao', JSON.stringify(solicitacoesAtualizadas));
                
                updateDevolucoesList();
                updateBooksGrid();
                alert('Devolução confirmada com sucesso!');
            } catch (error) {
                console.error('Erro ao confirmar devolução:', error);
                alert('Erro ao confirmar devolução. Por favor, tente novamente.');
            }
        }

        function rejeitarDevolucao(id) {
            try {
                // Obter dados necessários
                const solicitacoesDevolucao = JSON.parse(localStorage.getItem('solicitacoesDevolucao') || '[]');
                const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
                
                const solicitacao = solicitacoesDevolucao.find(s => s.id === id);
                if (!solicitacao) {
                    throw new Error('Solicitação não encontrada');
                }

                // Reverter status do empréstimo
                const emprestimo = emprestimos.find(e => e.id === solicitacao.emprestimoId);
                if (emprestimo) {
                    emprestimo.status = 'emprestado';
                    localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
                }

                // Remover solicitação
                const solicitacoesAtualizadas = solicitacoesDevolucao.filter(s => s.id !== id);
                localStorage.setItem('solicitacoesDevolucao', JSON.stringify(solicitacoesAtualizadas));
                
                updateDevolucoesList();
                alert('Devolução rejeitada.');
            } catch (error) {
                console.error('Erro ao rejeitar devolução:', error);
                alert('Erro ao rejeitar devolução. Por favor, tente novamente.');
            }
        }

        // Função para alternar o painel de devoluções
        function toggleDevolucoesPanel() {
            const panel = document.getElementById('devolucoesPanel');
            panel.classList.toggle('active');
            if (panel.classList.contains('active')) {
                updateDevolucoesList();
            }
        }

        // Atualizar lista de devoluções ao carregar a página
        window.addEventListener('load', function() {
            updateDevolucoesList();
            updateBooksGrid();
        });

        // Atualizar lista periodicamente
        setInterval(updateDevolucoesList, 5000);

        // Inicialização
        window.addEventListener('load', () => {
            // Lista de livros padrão
            const livrosPadrao = [
                {
                    id: 1,
                    title: "1984",
                    image: "IMG/1984.jpg",
                    estoque: 3,
                    autor: "George Orwell",
                    ano: 1949,
                    paginas: 328,
                    categoria: "Ficção Distópica",
                    descricao: "Uma distopia que retrata um futuro totalitário onde o governo exerce controle absoluto sobre a vida dos cidadãos."
                },
                {
                    id: 2,
                    title: "A Arte da Guerra",
                    image: "IMG/aartedaguerra.jpg",
                    estoque: 2,
                    autor: "Sun Tzu",
                    ano: -500,
                    paginas: 160,
                    categoria: "Estratégia",
                    descricao: "Um antigo tratado militar chinês que apresenta estratégias e táticas de guerra aplicáveis a diversos aspectos da vida."
                },
                {
                    id: 3,
                    title: "A Culpa é das Estrelas",
                    image: "IMG/aculpaedasestrelas.jpg",
                    estoque: 4,
                    autor: "John Green",
                    ano: 2012,
                    paginas: 288,
                    categoria: "Romance Jovem Adulto",
                    descricao: "Uma história emocionante sobre dois adolescentes com câncer que se apaixonam e vivem uma história de amor única."
                },
                {
                    id: 4,
                    title: "A Cabana",
                    image: "IMG/acabana.jpg",
                    estoque: 2,
                    autor: "William P. Young",
                    ano: 2007,
                    paginas: 240,
                    categoria: "Ficção Cristã",
                    descricao: "Um homem em luto recebe um misterioso convite para visitar uma cabana onde sua filha foi assassinada."
                },
                {
                    id: 5,
                    title: "A Menina que Roubava Livros",
                    image: "IMG/ameninaqueroubavalivros.jpg",
                    estoque: 3,
                    autor: "Markus Zusak",
                    ano: 2005,
                    paginas: 480,
                    categoria: "Ficção Histórica",
                    descricao: "Durante a Segunda Guerra Mundial, uma jovem alemã encontra conforto em roubar livros e compartilhá-los com outros."
                },
                {
                    id: 6,
                    title: "A Metamorfose",
                    image: "IMG/ametamorfose.webp",
                    estoque: 5,
                    autor: "Franz Kafka",
                    ano: 1915,
                    paginas: 104,
                    categoria: "Ficção",
                    descricao: "Um caixeiro-viajante acorda uma manhã transformado em um inseto gigante."
                },
                {
                    id: 7,
                    title: "A Revolução dos Bichos",
                    image: "IMG/arevolucaodosbichos.jpg",
                    estoque: 4,
                    autor: "George Orwell",
                    ano: 1945,
                    paginas: 152,
                    categoria: "Ficção Política",
                    descricao: "Uma fábula sobre animais que se rebelam contra seus donos humanos, uma alegoria à Revolução Russa."
                },
                {
                    id: 8,
                    title: "As Crônicas de Nárnia",
                    image: "IMG/ascronicasdenarnia.jpg",
                    estoque: 2,
                    autor: "C.S. Lewis",
                    ano: 1950,
                    paginas: 752,
                    categoria: "Fantasia",
                    descricao: "Uma série de aventuras em um mundo mágico acessível através de um guarda-roupa."
                },
                {
                    id: 9,
                    title: "Memórias Póstumas de Brás Cubas",
                    image: "IMG/asmemoriaspostumasdebrascuba.jpg",
                    estoque: 3,
                    autor: "Machado de Assis",
                    ano: 1881,
                    paginas: 256,
                    categoria: "Literatura Brasileira",
                    descricao: "Um defunto autor narra suas memórias e reflexões sobre sua vida."
                },
                {
                    id: 10,
                    title: "Capitães da Areia",
                    image: "IMG/capitaesdaareia.jpg",
                    estoque: 4,
                    autor: "Jorge Amado",
                    ano: 1937,
                    paginas: 256,
                    categoria: "Literatura Brasileira",
                    descricao: "A história de um grupo de meninos de rua em Salvador, suas aventuras e desventuras."
                },
                {
                    id: 11,
                    title: "Cem Anos de Solidão",
                    image: "IMG/cemanosdesolidao.jpg",
                    estoque: 2,
                    autor: "Gabriel García Márquez",
                    ano: 1967,
                    paginas: 448,
                    categoria: "Realismo Mágico",
                    descricao: "A saga da família Buendía ao longo de várias gerações em Macondo."
                },
                {
                    id: 12,
                    title: "Dom Casmurro",
                    image: "IMG/domcasmurro.webp",
                    estoque: 5,
                    autor: "Machado de Assis",
                    ano: 1899,
                    paginas: 208,
                    categoria: "Literatura Brasileira",
                    descricao: "Bentinho narra sua história de amor com Capitu e suas suspeitas de traição."
                },
                {
                    id: 13,
                    title: "Dom Quixote",
                    image: "IMG/domquixote.jpg",
                    estoque: 2,
                    autor: "Miguel de Cervantes",
                    ano: 1605,
                    paginas: 863,
                    categoria: "Romance de Cavalaria",
                    descricao: "As aventuras de um fidalgo que enlouquece após ler muitos romances de cavalaria."
                },
                {
                    id: 14,
                    title: "Grande Sertão: Veredas",
                    image: "IMG/grandesertaoveredas.jpg",
                    estoque: 3,
                    autor: "João Guimarães Rosa",
                    ano: 1956,
                    paginas: 624,
                    categoria: "Literatura Brasileira",
                    descricao: "A história do jagunço Riobaldo e sua jornada pelo sertão brasileiro."
                },
                {
                    id: 15,
                    title: "Harry Potter e a Pedra Filosofal",
                    image: "IMG/harrypotereapedrafilosofal.jpg",
                    estoque: 4,
                    autor: "J.K. Rowling",
                    ano: 1997,
                    paginas: 264,
                    categoria: "Fantasia",
                    descricao: "O início da jornada do jovem bruxo Harry Potter em Hogwarts."
                },
                {
                    id: 16,
                    title: "Macunaíma",
                    image: "IMG/macunaima.jpg",
                    estoque: 3,
                    autor: "Mário de Andrade",
                    ano: 1928,
                    paginas: 192,
                    categoria: "Literatura Brasileira",
                    descricao: "As aventuras do herói sem nenhum caráter, uma alegoria do povo brasileiro."
                },
                {
                    id: 17,
                    title: "O Alquimista",
                    image: "IMG/oalquimista.jpg",
                    estoque: 4,
                    autor: "Paulo Coelho",
                    ano: 1988,
                    paginas: 208,
                    categoria: "Ficção",
                    descricao: "A jornada de um pastor em busca de seu tesouro pessoal."
                },
                {
                    id: 18,
                    title: "O Código Da Vinci",
                    image: "IMG/ocodigodavinci.jpg",
                    estoque: 3,
                    autor: "Dan Brown",
                    ano: 2003,
                    paginas: 432,
                    categoria: "Suspense",
                    descricao: "Um professor de simbologia investiga um assassinato no Museu do Louvre."
                },
                {
                    id: 19,
                    title: "O Cortiço",
                    image: "IMG/ocortico.jpg",
                    estoque: 5,
                    autor: "Aluísio Azevedo",
                    ano: 1890,
                    paginas: 256,
                    categoria: "Literatura Brasileira",
                    descricao: "A vida dos moradores de um cortiço no Rio de Janeiro do século XIX."
                },
                {
                    id: 20,
                    title: "O Diário de Anne Frank",
                    image: "IMG/odiariodeannnefrank.jpg",
                    estoque: 3,
                    autor: "Anne Frank",
                    ano: 1947,
                    paginas: 352,
                    categoria: "Biografia",
                    descricao: "O diário de uma jovem judia durante a ocupação nazista na Holanda."
                },
                {
                    id: 21,
                    title: "O Hobbit",
                    image: "IMG/ohobbit.jpg",
                    estoque: 4,
                    autor: "J.R.R. Tolkien",
                    ano: 1937,
                    paginas: 336,
                    categoria: "Fantasia",
                    descricao: "A aventura do hobbit Bilbo Bolseiro em uma jornada para recuperar um tesouro roubado."
                },
                {
                    id: 22,
                    title: "O Nome do Vento",
                    image: "IMG/onomedovento.jpg",
                    estoque: 2,
                    autor: "Patrick Rothfuss",
                    ano: 2007,
                    paginas: 656,
                    categoria: "Fantasia",
                    descricao: "A história do lendário Kvothe, contada por ele mesmo."
                },
                {
                    id: 23,
                    title: "O Príncipe",
                    image: "IMG/oprincipe.jpg",
                    estoque: 4,
                    autor: "Nicolau Maquiavel",
                    ano: 1532,
                    paginas: 176,
                    categoria: "Política",
                    descricao: "Um tratado sobre política e poder, escrito para Lorenzo de Médici."
                },
                {
                    id: 24,
                    title: "Orgulho e Preconceito",
                    image: "IMG/orgulhoepreconceito.jpg",
                    estoque: 3,
                    autor: "Jane Austen",
                    ano: 1813,
                    paginas: 424,
                    categoria: "Romance",
                    descricao: "A história de Elizabeth Bennet e Mr. Darcy em uma sociedade regida por convenções sociais."
                },
                {
                    id: 25,
                    title: "O Senhor dos Anéis",
                    image: "IMG/osenhordosaneis.webp",
                    estoque: 2,
                    autor: "J.R.R. Tolkien",
                    ano: 1954,
                    paginas: 1200,
                    categoria: "Fantasia",
                    descricao: "A épica jornada para destruir o Um Anel e derrotar o Senhor do Escuro."
                },
                {
                    id: 26,
                    title: "Percy Jackson e o Ladrão de Raios",
                    image: "IMG/percyjacksoneoladraoderaios.jpg",
                    estoque: 4,
                    autor: "Rick Riordan",
                    ano: 2005,
                    paginas: 400,
                    categoria: "Fantasia Jovem",
                    descricao: "Um garoto descobre que é filho de um deus grego e precisa impedir uma guerra entre os deuses."
                },
                {
                    id: 27,
                    title: "O Pequeno Príncipe",
                    image: "IMG/pequenoprincipe.jpg",
                    estoque: 5,
                    autor: "Antoine de Saint-Exupéry",
                    ano: 1943,
                    paginas: 96,
                    categoria: "Literatura Infantil",
                    descricao: "A história de um príncipe que viaja pelos planetas e aprende sobre amor e amizade."
                },
                {
                    id: 28,
                    title: "Vidas Secas",
                    image: "IMG/vidassecas.jpg",
                    estoque: 3,
                    autor: "Graciliano Ramos",
                    ano: 1938,
                    paginas: 176,
                    categoria: "Literatura Brasileira",
                    descricao: "A saga de uma família de retirantes pelo sertão nordestino."
                }
            ];

            // Carregar livros do localStorage
            const livrosExistentes = JSON.parse(localStorage.getItem('livros') || '[]');
            
            // Se não houver livros no localStorage, inicializar com a lista padrão
            if (livrosExistentes.length === 0) {
                localStorage.setItem('livros', JSON.stringify(livrosPadrao));
                books.length = 0;
                books.push(...livrosPadrao);
            } else {
                books.length = 0;
                books.push(...livrosExistentes);
            }

            updateBooksGrid();
            updateDonationsList();
            updateDevolucoesList();
        });

        // Fechar modal quando clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('bookModal');
            if (event.target === modal) {
                closeModal();
            }
        }


    </script>
</body>
</html> 