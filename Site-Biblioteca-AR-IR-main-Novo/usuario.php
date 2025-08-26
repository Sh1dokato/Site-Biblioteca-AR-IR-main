<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca Arco-Íris - Usuário</title>
  <link rel="icon" href="favicon.ico">
  <link rel="stylesheet" href="CSS/usuario.css">
</head>
<body style="background-image: url(IMG/fundo.png);
             background-size: cover;
             background-position: center;
             background-repeat: no-repeat;" >
  <header class="header">
    <div class="header-title">
      <img src="IMG/logo.png" alt="Logo" style="height: 30px;">
      <span>Biblioteca Arco-Íris</span>
    </div>
    <form style="display: flex; align-items: center; gap: 8px;" onsubmit="event.preventDefault();">
      <input type="text" placeholder="Pesquisar livros..." style="padding: 8px 16px; border-radius: 4px; border: none; font-size: 16px; outline: none; width: 300px;" id="searchInput">
    </form>
    <div class="header-buttons">
      <a href="emprestimos.php" class="header-btn">Meus Empréstimos</a>
      <a href="perfil.php" class="header-btn">Perfil</a>
      <a href="index.php" class="header-btn">Sair</a>
    </div>
  </header>

  <div class="carousel-container">
    <div class="carousel">
      <img src="IMG/cemanosdesolidao.jpg" alt="Banner 1">
      <img src="IMG/ohobbit.jpg" alt="Banner 2">
      <img src="IMG/acabana.jpg" alt="Banner 3">
      <img src="IMG/ascronicasdenarnia.jpg" alt="Banner 4">
      <img src="IMG/1984.jpg" alt="Banner 5">
      <img src="IMG/domquixote.jpg" alt="Banner 6">
      <img src="IMG/orgulhoepreconceito.jpg" alt="Banner 7">
      <img src="IMG/osenhordosaneis.webp" alt="Banner 8">
      <img src="IMG/ametamorfose.webp" alt="Banner 9">
      <img src="IMG/arevolucaodosbichos.jpg" alt="Banner 10">
      <!-- Duplicar as imagens para criar o loop infinito -->
      <img src="IMG/cemanosdesolidao.jpg" alt="Banner 1">
      <img src="IMG/ohobbit.jpg" alt="Banner 2">
      <img src="IMG/acabana.jpg" alt="Banner 3">
      <img src="IMG/ascronicasdenarnia.jpg" alt="Banner 4">
      <img src="IMG/1984.jpg" alt="Banner 5">
      <img src="IMG/domquixote.jpg" alt="Banner 6">
      <img src="IMG/orgulhoepreconceito.jpg" alt="Banner 7">
      <img src="IMG/osenhordosaneis.webp" alt="Banner 8">
      <img src="IMG/ametamorfose.webp" alt="Banner 9">
      <img src="IMG/arevolucaodosbichos.jpg" alt="Banner 10">
      <img src="IMG/cemanosdesolidao.jpg" alt="Banner 1">
      <img src="IMG/ohobbit.jpg" alt="Banner 2">
      <img src="IMG/acabana.jpg" alt="Banner 3">
      <img src="IMG/ascronicasdenarnia.jpg" alt="Banner 4">
      <img src="IMG/1984.jpg" alt="Banner 5">
      <img src="IMG/domquixote.jpg" alt="Banner 6">
      <img src="IMG/orgulhoepreconceito.jpg" alt="Banner 7">
      <img src="IMG/osenhordosaneis.webp" alt="Banner 8">
      <img src="IMG/ametamorfose.webp" alt="Banner 9">
      <img src="IMG/arevolucaodosbichos.jpg" alt="Banner 10">
      <!-- Duplicar as imagens para criar o loop infinito -->
      <img src="IMG/cemanosdesolidao.jpg" alt="Banner 1">
      <img src="IMG/ohobbit.jpg" alt="Banner 2">
      <img src="IMG/acabana.jpg" alt="Banner 3">
      <img src="IMG/ascronicasdenarnia.jpg" alt="Banner 4">
      <img src="IMG/1984.jpg" alt="Banner 5">
      <img src="IMG/domquixote.jpg" alt="Banner 6">
      <img src="IMG/orgulhoepreconceito.jpg" alt="Banner 7">
      <img src="IMG/osenhordosaneis.webp" alt="Banner 8">
      <img src="IMG/ametamorfose.webp" alt="Banner 9">
      <img src="IMG/arevolucaodosbichos.jpg" alt="Banner 10">
    </div>
  </div>

  <div class="books-container" id="booksContainer">
    <!-- Os cards de livros serão inseridos aqui pelo JS -->
  </div>

  <script>
    // Carregar livros do localStorage ou usar padrão
    let livros = [];
    if (localStorage.getItem('livros')) {
      livros = JSON.parse(localStorage.getItem('livros'));
      // Adaptar para o formato esperado (caso necessário)
      livros = livros.map(livro => ({
        titulo: livro.title || livro.titulo,
        imagem: livro.image || livro.imagem,
        disponiveis: livro.estoque !== undefined ? livro.estoque : livro.disponiveis,
        autor: livro.autor,
        descricao: livro.descricao
      }));
    } else {
      livros = [
        { titulo: '1984', imagem: 'IMG/1984.jpg', disponiveis: 5, autor: 'George Orwell', descricao: 'Um romance distópico sobre um regime totalitário que vigia e controla tudo.' },
        { titulo: 'A Arte da Guerra', imagem: 'IMG/aartedaguerra.jpg', disponiveis: 3, autor: 'Sun Tzu', descricao: 'Um tratado militar chinês sobre estratégia e táticas de guerra.' },
        { titulo: 'A Cabana', imagem: 'IMG/acabana.jpg', disponiveis: 4, autor: 'William P. Young', descricao: 'Uma história de superação, fé e perdão após uma tragédia familiar.' },
        { titulo: 'A Culpa é das Estrelas', imagem: 'IMG/aculpaedasestrelas.jpg', disponiveis: 6, autor: 'John Green', descricao: 'Dois adolescentes com câncer se apaixonam e vivem um amor inesquecível.' },
        { titulo: 'A Menina que Roubava Livros', imagem: 'IMG/ameninaqueroubavalivros.jpg', disponiveis: 2, autor: 'Markus Zusak', descricao: 'Durante a Segunda Guerra Mundial, uma menina encontra consolo nos livros.' },
        { titulo: 'A Metamorfose', imagem: 'IMG/ametamorfose.webp', disponiveis: 3, autor: 'Franz Kafka', descricao: 'Um homem acorda transformado em um inseto monstruoso.' },
        { titulo: 'A Revolução dos Bichos', imagem: 'IMG/arevolucaodosbichos.jpg', disponiveis: 4, autor: 'George Orwell', descricao: 'Uma sátira sobre o poder e a corrupção em uma fazenda de animais.' },
        { titulo: 'As Crônicas de Nárnia', imagem: 'IMG/ascronicasdenarnia.jpg', disponiveis: 2, autor: 'C. S. Lewis', descricao: 'Uma série de aventuras fantásticas em um mundo mágico.' },
        { titulo: 'Memórias Póstumas de Brás Cubas', imagem: 'IMG/asmemoriaspostumasdebrascuba.jpg', disponiveis: 1, autor: 'Machado de Assis', descricao: 'A vida e reflexões de Brás Cubas, narradas após sua morte.' },
        { titulo: 'Capitães da Areia', imagem: 'IMG/capitaesdaareia.jpg', disponiveis: 2, autor: 'Jorge Amado', descricao: 'A história de meninos de rua em Salvador.' },
        { titulo: 'Cem Anos de Solidão', imagem: 'IMG/cemanosdesolidao.jpg', disponiveis: 3, autor: 'Gabriel García Márquez', descricao: 'A saga da família Buendía na fictícia Macondo.' },
        { titulo: 'Dom Casmurro', imagem: 'IMG/domcasmurro.webp', disponiveis: 2, autor: 'Machado de Assis', descricao: 'Bentinho narra sua vida e o ciúme por Capitu.' },
        { titulo: 'Dom Quixote', imagem: 'IMG/domquixote.jpg', disponiveis: 2, autor: 'Miguel de Cervantes', descricao: 'As aventuras do cavaleiro Dom Quixote e seu fiel escudeiro Sancho Pança.' },
        { titulo: 'Elon Musk', imagem: 'IMG/elonmusk.jpg', disponiveis: 1, autor: 'Ashlee Vance', descricao: 'A biografia do empreendedor Elon Musk.' },
        { titulo: 'Grande Sertão: Veredas', imagem: 'IMG/grandesertaoveredas.jpg', disponiveis: 2, autor: 'João Guimarães Rosa', descricao: 'A saga de Riobaldo no sertão brasileiro.' },
        { titulo: 'Harry Potter e a Pedra Filosofal', imagem: 'IMG/harrypotereapedrafilosofal.jpg', disponiveis: 3, autor: 'J.K. Rowling', descricao: 'O início da jornada de Harry Potter no mundo da magia.' },
        { titulo: 'O Alquimista', imagem: 'IMG/oalquimista.jpg', disponiveis: 2, autor: 'Paulo Coelho', descricao: 'Um jovem pastor parte em busca de um tesouro e de si mesmo.' },
        { titulo: 'O Código Da Vinci', imagem: 'IMG/ocodigodavinci.jpg', disponiveis: 2, autor: 'Dan Brown', descricao: 'Um thriller envolvendo simbologia, arte e religião.' },
        { titulo: 'O Cortiço', imagem: 'IMG/ocortico.jpg', disponiveis: 2, autor: 'Aluísio Azevedo', descricao: 'A vida dos moradores de um cortiço no Rio de Janeiro.' },
        { titulo: 'O Diário de Anne Frank', imagem: 'IMG/odiariodeannnefrank.jpg', disponiveis: 1, autor: 'Anne Frank', descricao: 'O relato emocionante de uma jovem judia durante a Segunda Guerra.' },
        { titulo: 'O Hobbit', imagem: 'IMG/ohobbit.jpg', disponiveis: 2, autor: 'J.R.R. Tolkien', descricao: 'A aventura de Bilbo Bolseiro em busca de um tesouro guardado por um dragão.' },
        { titulo: 'O Nome do Vento', imagem: 'IMG/onomedovento.jpg', disponiveis: 2, autor: 'Patrick Rothfuss', descricao: 'A história de Kvothe, um homem lendário.' },
        { titulo: 'O Príncipe', imagem: 'IMG/oprincipe.jpg', disponiveis: 1, autor: 'Nicolau Maquiavel', descricao: 'Um tratado político sobre poder e liderança.' },
        { titulo: 'Orgulho e Preconceito', imagem: 'IMG/orgulhoepreconceito.jpg', disponiveis: 2, autor: 'Jane Austen', descricao: 'O romance entre Elizabeth Bennet e Mr. Darcy.' },
        { titulo: 'O Senhor dos Anéis', imagem: 'IMG/osenhordosaneis.webp', disponiveis: 2, autor: 'J.R.R. Tolkien', descricao: 'A épica jornada para destruir o Um Anel.' },
        { titulo: 'Percy Jackson e o Ladrão de Raios', imagem: 'IMG/percyjacksoneoladraoderaios.jpg', disponiveis: 2, autor: 'Rick Riordan', descricao: 'O início das aventuras do semideus Percy Jackson.' },
        { titulo: 'Vidas Secas', imagem: 'IMG/vidassecas.jpg', disponiveis: 1, autor: 'Graciliano Ramos', descricao: 'A luta de uma família sertaneja contra a seca.' },
        { titulo: 'Macunaíma', imagem: 'IMG/macunaima.jpg', disponiveis: 1, autor: 'Mário de Andrade', descricao: 'A saga do herói sem nenhum caráter.' }
      ];
    }

    function renderBooks(filter = '') {
      const container = document.getElementById('booksContainer');
      container.innerHTML = '';
      livros.filter(livro => livro.titulo.toLowerCase().includes(filter.toLowerCase())).forEach((livro, idx) => {
        container.innerHTML += `
          <div class="book-card">
            <img src="${livro.imagem}" alt="${livro.titulo}" class="book-cover">
            <div class="book-title">${livro.titulo}</div>
            <button class="ver-mais-btn" onclick="abrirModalLivro(${idx})">Ver mais</button>
            <div style="color: white; font-size: 13px; margin-top: 6px;">Disponíveis: ${livro.disponiveis}</div>
          </div>
        `;
      });
    }

    // Modal HTML
    document.body.insertAdjacentHTML('beforeend', `
      <div id="modalLivro" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:10px; max-width:400px; width:90vw; padding:30px 20px 20px 20px; position:relative; box-shadow:0 4px 24px rgba(0,0,0,0.2);">
          <span id="fecharModalLivro" style="position:absolute; top:10px; right:18px; font-size:28px; color:#888; cursor:pointer;">&times;</span>
          <img id="modalLivroImg" src="" alt="" style="width:120px; height:160px; object-fit:cover; border-radius:5px; display:block; margin:0 auto 10px auto;">
          <h2 id="modalLivroTitulo" style="text-align:center; margin:10px 0 5px 0; font-size:22px;"></h2>
          <div id="modalLivroAutor" style="text-align:center; color:#666; font-size:15px; margin-bottom:10px;"></div>
          <div id="modalLivroDescricao" style="font-size:15px; color:#333; margin-bottom:18px; text-align:center;"></div>
          <div style="text-align:center;">
            <button id="btnEmprestarLivro" style="background:#ff9000; color:white; border:none; border-radius:5px; padding:10px 30px; font-size:16px; cursor:pointer;">Emprestar livro</button>
            <div id="modalLivroEstoque" style="margin-top:10px; color:#888; font-size:14px;"></div>
          </div>
        </div>
      </div>
    `);

    let livroAtualIdx = null;
    window.abrirModalLivro = function(idx) {
      livroAtualIdx = idx;
      const livro = livros[idx];
      document.getElementById('modalLivroImg').src = livro.imagem;
      document.getElementById('modalLivroTitulo').textContent = livro.titulo;
      document.getElementById('modalLivroAutor').textContent = 'Autor: ' + livro.autor;
      document.getElementById('modalLivroDescricao').textContent = livro.descricao;
      document.getElementById('modalLivroEstoque').textContent = 'Disponíveis: ' + livro.disponiveis;
      const btn = document.getElementById('btnEmprestarLivro');
      btn.disabled = livro.disponiveis < 1;
      btn.textContent = livro.disponiveis > 0 ? 'Emprestar livro' : 'Indisponível';
      document.getElementById('modalLivro').style.display = 'flex';
    };
    document.getElementById('fecharModalLivro').onclick = function() {
      document.getElementById('modalLivro').style.display = 'none';
    };
    document.getElementById('modalLivro').onclick = function(e) {
      if (e.target === this) this.style.display = 'none';
    };
    document.getElementById('btnEmprestarLivro').onclick = function() {
      if (livroAtualIdx === null) return;
      if (livros[livroAtualIdx].disponiveis < 1) return;
      const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
      if (!usuarioLogado) {
        alert('Faça login para emprestar livros.');
        return;
      }
      const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
      // Verificar se o usuário já tem 5 empréstimos ativos
      const emprestimosAtivos = emprestimos.filter(e => e.userId === usuarioLogado.id && !e.returned && e.status !== 'devolvido');
      if (emprestimosAtivos.length >= 5) {
        alert('Você só pode ter até 5 empréstimos ativos por vez.');
        return;
      }
      // Verificar se o usuário já tem este livro emprestado
      const jaTemEsteLivro = emprestimosAtivos.some(e => (e.bookTitle || e.titulo) === livros[livroAtualIdx].titulo && e.status !== 'devolvido' && !e.returned);
      if (jaTemEsteLivro) {
        alert('Você já possui este livro emprestado. Devolva antes de pegar novamente.');
        return;
      }
      livros[livroAtualIdx].disponiveis--;
      // Atualizar estoque no localStorage
      if (localStorage.getItem('livros')) {
        let livrosStorage = JSON.parse(localStorage.getItem('livros'));
        let livroStorage = livrosStorage.find(l => (l.title || l.titulo) === livros[livroAtualIdx].titulo);
        if (livroStorage) {
          if (livroStorage.estoque !== undefined) {
            livroStorage.estoque--;
          } else if (livroStorage.disponiveis !== undefined) {
            livroStorage.disponiveis--;
          }
          localStorage.setItem('livros', JSON.stringify(livrosStorage));
        }
      }
      renderBooks(document.getElementById('searchInput').value);
      window.abrirModalLivro(livroAtualIdx);
      const hoje = new Date();
      const devolucao = new Date();
      devolucao.setDate(hoje.getDate() + 7); // 7 dias de empréstimo
      const novoEmprestimo = {
        id: Date.now(),
        userId: usuarioLogado.id,
        bookTitle: livros[livroAtualIdx].titulo,
        bookImage: livros[livroAtualIdx].imagem,
        loanDate: hoje.toISOString(),
        dueDate: devolucao.toISOString(),
        dataEmprestimo: hoje.toLocaleDateString('pt-BR'),
        dataDevolucao: devolucao.toLocaleDateString('pt-BR'),
        status: 'emprestado',
        returned: false,
        debtAmount: 0,
        renovado: false
      };
      emprestimos.push(novoEmprestimo);
      localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
      alert('Livro emprestado com sucesso!');
    };

    renderBooks();
    document.getElementById('searchInput').addEventListener('input', function() {
      renderBooks(this.value);
    });
  </script>
</body>
</html> 