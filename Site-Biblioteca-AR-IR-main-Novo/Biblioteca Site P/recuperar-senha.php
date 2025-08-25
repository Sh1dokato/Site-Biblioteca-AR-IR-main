<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Senha - Biblioteca Arco-Íris</title>
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
      <form class="form-box" id="recuperarSenhaForm">
        <div class="input-group">
          <span class="icon">📚</span>
          <input type="text" name="cpf" placeholder="CPF (somente números)" pattern="\d*" maxlength="11" required>
        </div>
        <div class="input-group">
          <span class="icon">📞</span>
          <input type="tel" name="telefone" placeholder="Telefone" required>
        </div>
        <div class="input-group">
          <span class="icon">🔒</span>
          <input type="password" name="novaSenha" placeholder="Nova Senha" required>
          <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
        </div>
        <button type="submit" class="btn">MUDAR SENHA</button>
        <div class="links" style="display: flex; justify-content: center;">
          <a href="login.html" class="btn">VOLTAR</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('recuperarSenhaForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      // Verificar se todos os campos foram preenchidos
      const cpf = formData.get('cpf');
      const telefone = formData.get('telefone');
      const novaSenha = formData.get('novaSenha');

      if (cpf && telefone && novaSenha) {
        // Carregar usuários do localStorage
        const users = JSON.parse(localStorage.getItem('users')) || [];
        
        // Encontrar o usuário pelo CPF e telefone
        const userIndex = users.findIndex(user => user.cpf === cpf && user.phone === telefone);
        
        if (userIndex !== -1) {
          // Atualizar a senha do usuário
          users[userIndex].senha = novaSenha;
          
          // Salvar a lista atualizada no localStorage
          localStorage.setItem('users', JSON.stringify(users));
          
        alert('Senha alterada com sucesso!');
        window.location.href = 'login.html';
        } else {
          alert('CPF ou telefone não encontrados!');
        }
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
  </script>
</body>
</html> 