<?php
require_once 'PHP/database.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Registro - Biblioteca Arco-Íris</title>
  <link rel="icon" href="favicon.ico">
  <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>
  <div class="container">
    <div class="form-container">
      <div class="arco-iris">
        <span>B</span><span>I</span><span>B</span><span>L</span><span>I</span><span>O</span><span>T</span><span>E</span><span>C</span><span>A</span>
        <br>
        <span>A</span><span>R</span><span>C</span><span>O</span><span>-</span><span>Í</span><span>R</span><span>I</span><span>S</span>
      </div>
      <form class="form-box" id="registroForm" method="POST" action="PHP/auth.php">
        <div class="input-group">
          <span class="icon">📚</span>
          <input type="text" name="cpf" placeholder="CPF (somente números)" pattern="\d*" maxlength="11" required>
        </div>
        <div class="input-group">
          <span class="icon">📞</span>
          <input type="tel" name="telefone" placeholder="Telefone" maxlength="11" required>
        </div>
        <div class="input-group">
          <span class="icon">🔒</span>
          <input type="password" name="senha" placeholder="Senha" required>
          <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
        </div>
        <div class="input-group">
          <span class="icon">👤</span>
          <input type="text" name="nome" placeholder="Nome" required>
        </div>
        <button type="submit" class="btn">REGISTRAR</button>
        <div class="links" style="display: flex; justify-content: center;">
          <a href="login.php" class="btn">VOLTAR</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('registroForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      if (formData.get('cpf') && formData.get('telefone') && 
          formData.get('senha') && formData.get('nome')) {
        
        // Adicionar ação para o PHP
        formData.append('acao', 'registrar');
        
        // Enviar dados para o PHP
        fetch('PHP/auth.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Registro realizado com sucesso!');
            window.location.href = 'login.php';
          } else {
            alert('Erro: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          alert('Erro ao conectar com o servidor. Tente novamente.');
        });
        
      } else {
        alert('Por favor, preencha todos os campos!');
      }
    });

    // Validação do CPF (apenas números)
    document.querySelector('input[name="cpf"]').addEventListener('input', function(e) {
      this.value = this.value.replace(/\D/g, '');
    });

    // Validação do telefone (apenas números)
    document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
      this.value = this.value.replace(/\D/g, '');
    });

    // Função para mostrar/ocultar senha
    function togglePassword(button) {
      const input = button.previousElementSibling;
      if (input.type === "password") {
        input.type = "text";
        button.textContent = "👁️‍🗨️";
      } else {
        input.type = "password";
        button.textContent = "👁️";
      }
    }

    // Lista padrão de livros
    const livrosPadrao = [
        { id: 1, title: "1984", image: "IMG/1984.jpg", estoque: 5, autor: "George Orwell", ano: 1949, paginas: 328, categoria: "Ficção Científica", descricao: "Um clássico distópico sobre um regime totalitário." },
        { id: 2, title: "A Arte da Guerra", image: "IMG/aartedaguerra.jpg", estoque: 3, autor: "Sun Tzu", ano: -500, paginas: 273, categoria: "Estratégia", descricao: "Antigo tratado militar chinês sobre estratégia e tática." },
        { id: 3, title: "A Cabana", image: "IMG/acabana.jpg", estoque: 4, autor: "William P. Young", ano: 2007, paginas: 240, categoria: "Ficção", descricao: "Uma história de superação e fé após uma tragédia." },
        { id: 4, title: "A Culpa é das Estrelas", image: "IMG/aculpaedasestrelas.jpg", estoque: 6, autor: "John Green", ano: 2012, paginas: 288, categoria: "Romance", descricao: "Dois adolescentes se apaixonam enquanto lutam contra o câncer." },
        { id: 5, title: "A Menina que Roubava Livros", image: "IMG/ameninaqueroubavalivros.jpg", estoque: 2, autor: "Markus Zusak", ano: 2005, paginas: 480, categoria: "Drama", descricao: "A história de uma garota na Alemanha nazista que encontra consolo nos livros." },
        { id: 6, title: "A Metamorfose", image: "IMG/ametamorfose.webp", estoque: 3, autor: "Franz Kafka", ano: 1915, paginas: 100, categoria: "Ficção", descricao: "Um homem acorda transformado em um inseto gigante." },
        { id: 7, title: "A Revolução dos Bichos", image: "IMG/arevolucaodosbichos.jpg", estoque: 4, autor: "George Orwell", ano: 1945, paginas: 152, categoria: "Fábula Política", descricao: "Animais de uma fazenda se rebelam contra seus donos humanos." },
        { id: 8, title: "As Crônicas de Nárnia", image: "IMG/ascronicasdenarnia.jpg", estoque: 5, autor: "C.S. Lewis", ano: 1950, paginas: 768, categoria: "Fantasia", descricao: "Aventuras mágicas em um mundo fantástico." },
        { id: 9, title: "Memórias Póstumas de Brás Cubas", image: "IMG/asmemoriaspostumasdebrascuba.jpg", estoque: 2, autor: "Machado de Assis", ano: 1881, paginas: 208, categoria: "Romance", descricao: "Narrativa inovadora de um defunto-autor." },
        { id: 10, title: "Capitães da Areia", image: "IMG/capitaesdaareia.jpg", estoque: 3, autor: "Jorge Amado", ano: 1937, paginas: 256, categoria: "Literatura Brasileira", descricao: "A vida de meninos de rua em Salvador." },
        { id: 11, title: "Cem Anos de Solidão", image: "IMG/cemanosdesolidao.jpg", estoque: 4, autor: "Gabriel García Márquez", ano: 1967, paginas: 448, categoria: "Realismo Mágico", descricao: "A saga da família Buendía em Macondo." },
        { id: 12, title: "Dom Casmurro", image: "IMG/domcasmurro.webp", estoque: 2, autor: "Machado de Assis", ano: 1899, paginas: 256, categoria: "Romance", descricao: "A dúvida sobre a traição de Capitu." },
        { id: 13, title: "Dom Quixote", image: "IMG/domquixote.jpg", estoque: 3, autor: "Miguel de Cervantes", ano: 1605, paginas: 992, categoria: "Romance", descricao: "As aventuras do cavaleiro da triste figura." },
        { id: 14, title: "Elon Musk", image: "IMG/elonmusk.jpg", estoque: 2, autor: "Ashlee Vance", ano: 2015, paginas: 416, categoria: "Biografia", descricao: "A vida e carreira do empreendedor Elon Musk." },
        { id: 15, title: "Harry Potter e a Pedra Filosofal", image: "IMG/harrypotereapedrafilosofal.jpg", estoque: 5, autor: "J.K. Rowling", ano: 1997, paginas: 264, categoria: "Fantasia", descricao: "O início da jornada do jovem bruxo Harry Potter em Hogwarts." },
        { id: 16, title: "Macunaíma", image: "IMG/macunaima.jpg", estoque: 3, autor: "Mário de Andrade", ano: 1928, paginas: 192, categoria: "Literatura Brasileira", descricao: "As aventuras do herói sem nenhum caráter, uma alegoria do povo brasileiro." },
        { id: 17, title: "O Alquimista", image: "IMG/oalquimista.jpg", estoque: 4, autor: "Paulo Coelho", ano: 1988, paginas: 208, categoria: "Ficção", descricao: "A jornada de um pastor em busca de seu tesouro pessoal." },
        { id: 18, title: "O Código Da Vinci", image: "IMG/ocodigodavinci.jpg", estoque: 3, autor: "Dan Brown", ano: 2003, paginas: 432, categoria: "Suspense", descricao: "Um professor de simbologia investiga um assassinato no Museu do Louvre." },
        { id: 19, title: "O Cortiço", image: "IMG/ocortico.jpg", estoque: 5, autor: "Aluísio Azevedo", ano: 1890, paginas: 256, categoria: "Literatura Brasileira", descricao: "A vida dos moradores de um cortiço no Rio de Janeiro do século XIX." },
        { id: 20, title: "O Diário de Anne Frank", image: "IMG/odiariodeannnefrank.jpg", estoque: 3, autor: "Anne Frank", ano: 1947, paginas: 352, categoria: "Biografia", descricao: "O diário de uma jovem judia durante a ocupação nazista na Holanda." },
        { id: 21, title: "O Hobbit", image: "IMG/ohobbit.jpg", estoque: 4, autor: "J.R.R. Tolkien", ano: 1937, paginas: 336, categoria: "Fantasia", descricao: "A aventura do hobbit Bilbo Bolseiro em uma jornada para recuperar um tesouro roubado." },
        { id: 22, title: "O Nome do Vento", image: "IMG/onomedovento.jpg", estoque: 2, autor: "Patrick Rothfuss", ano: 2007, paginas: 656, categoria: "Fantasia", descricao: "A história do lendário Kvothe, contada por ele mesmo." },
        { id: 23, title: "O Príncipe", image: "IMG/oprincipe.jpg", estoque: 4, autor: "Nicolau Maquiavel", ano: 1532, paginas: 176, categoria: "Política", descricao: "Um tratado sobre política e poder, escrito para Lorenzo de Médici." },
        { id: 24, title: "Orgulho e Preconceito", image: "IMG/orgulhoepreconceito.jpg", estoque: 3, autor: "Jane Austen", ano: 1813, paginas: 424, categoria: "Romance", descricao: "A história de Elizabeth Bennet e Mr. Darcy em uma sociedade regida por convenções sociais." },
        { id: 25, title: "O Senhor dos Anéis", image: "IMG/osenhordosaneis.webp", estoque: 2, autor: "J.R.R. Tolkien", ano: 1954, paginas: 1200, categoria: "Fantasia", descricao: "A épica jornada para destruir o Um Anel e derrotar o Senhor do Escuro." },
        { id: 26, title: "Percy Jackson e o Ladrão de Raios", image: "IMG/percyjacksoneoladraoderaios.jpg", estoque: 4, autor: "Rick Riordan", ano: 2005, paginas: 400, categoria: "Fantasia Jovem", descricao: "Um garoto descobre que é filho de um deus grego e precisa impedir uma guerra entre os deuses." },
        { id: 27, title: "O Pequeno Príncipe", image: "IMG/pequenoprincipe.jpg", estoque: 5, autor: "Antoine de Saint-Exupéry", ano: 1943, paginas: 96, categoria: "Literatura Infantil", descricao: "A história de um príncipe que viaja pelos planetas e aprende sobre amor e amizade." },
        { id: 28, title: "Vidas Secas", image: "IMG/vidassecas.jpg", estoque: 3, autor: "Graciliano Ramos", ano: 1938, paginas: 176, categoria: "Literatura Brasileira", descricao: "A saga de uma família de retirantes pelo sertão nordestino." }
    ];
    // Inicializar livros se não existir
    if (!localStorage.getItem('livros')) {
      localStorage.setItem('livros', JSON.stringify(livrosPadrao));
    }
  </script>
</body>
</html> 