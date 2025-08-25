<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Biblioteca Arco-Íris</title>
  <link rel="icon" href="favicon.ico">
  <link rel="stylesheet" href="CSS/styles.css">
  <style>

  </style>
</head>
<body>
  <button class="help-btn" onclick="showHelpModal()">?</button>

  <div id="helpModal" class="help-modal">
    <div class="help-modal-content">
      <span class="close-help" onclick="closeHelpModal()">&times;</span>
      <h2>Precisa de ajuda?</h2>
      <p class="help-text">
        Se você está tendo problemas para acessar o sistema ou tem alguma dúvida,
        entre em contato conosco através do e-mail:
        <br><br>
        <a href="mailto:suporte@bibliotecaarcoiris.com" class="help-email">
          suporte@bibliotecaarcoiris.com
        </a>
      </p>
    </div>
  </div>
<div>
  <a class="voltar" href="index.html">Voltar</a>
</div>
  <div class="container">
    <div class="form-container">
      <div class="arco-iris">
        <span>B</span><span>I</span><span>B</span><span>L</span><span>I</span><span>O</span><span>T</span><span>E</span><span>C</span><span>A</span>
        <br>
        <span>A</span><span>R</span><span>C</span><span>O</span><span>-</span><span>Í</span><span>R</span><span>I</span><span>S</span>
      </div>
      <form class="form-box" id="loginForm">
        <div class="input-group">
          <span class="icon">📚</span>
          <input type="text" id="cpf" name="cpf" placeholder="CPF (000.000.000-00)" maxlength="14" required>
        </div>
        <div class="input-group">
          <span class="icon">🔒</span>
          <input type="password" id="senha" name="senha" placeholder="Senha" required>
          <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
        </div>
        <div class="input-group">
          <span class="icon">📞</span>
          <input type="tel" id="telefone" name="telefone" placeholder="Telefone (00) 00000-0000" maxlength="15" required>
        </div>
        <div class="links">
          <a href="recuperar-senha.html">Esqueceu a senha?</a>
          <button type="submit" class="btn">Entrar</button>
        </div>
        <div class="links" style="display: flex; justify-content: center; gap: 20px;">
          <a href="#" class="btn" id="btnVisitante">ENTRAR COMO VISITANTE</a>
          <a href="registro.html" class="btn">REGISTRAR <br> USUARIO</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Funções do modal de ajuda
    function showHelpModal() {
      document.getElementById('helpModal').style.display = 'block';
    }

    function closeHelpModal() {
      document.getElementById('helpModal').style.display = 'none';
    }

    // Fechar modal ao clicar fora dele
    window.onclick = function(event) {
      const modal = document.getElementById('helpModal');
      if (event.target === modal) {
        closeHelpModal();
      }
    }

    // Função para fazer login
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      // Remover máscaras antes de enviar
      const cpf = formData.get('cpf').replace(/\D/g, '');
      const telefone = formData.get('telefone').replace(/\D/g, '');
      
      // Criar novo FormData com dados limpos
      const cleanFormData = new FormData();
      cleanFormData.append('acao', 'login');
      cleanFormData.append('cpf', cpf);
      cleanFormData.append('senha', formData.get('senha'));
      cleanFormData.append('telefone', telefone);

      // Enviar dados para o PHP
      fetch('PHP/auth.php', {
        method: 'POST',
        body: cleanFormData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Salvar informações do usuário logado
          localStorage.setItem('usuarioLogado', JSON.stringify({
            id: data.usuario.id,
            nome: data.usuario.nome,
            cpf: cpf,
            telefone: telefone,
            isAdmin: data.usuario.is_admin,
            hasDebt: data.usuario.tem_debito,
            hasPendingDonation: data.usuario.tem_doacao_pendente
          }));

          // Redirecionar com base no tipo de usuário
          if (data.usuario.is_admin) {
            window.location.href = 'inicio-admin.html';
          } else {
            window.location.href = 'usuario.html';
          }
        } else {
          alert('Erro: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao conectar com o servidor. Tente novamente.');
      });
    });

    // Função para aplicar máscara de CPF
    function aplicarMascaraCPF(input) {
      let value = input.value.replace(/\D/g, '');
      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d)/, '$1.$2');
      value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      input.value = value;
    }

    // Função para aplicar máscara de telefone
    function aplicarMascaraTelefone(input) {
      let value = input.value.replace(/\D/g, '');
      if (value.length === 11) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
      } else if (value.length === 10) {
        value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
      }
      input.value = value;
    }

    // Aplicar máscaras em tempo real
    document.querySelector('input[name="cpf"]').addEventListener('input', function(e) {
      aplicarMascaraCPF(this);
    });

    document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
      aplicarMascaraTelefone(this);
    });

    // Botão de visitante
    document.getElementById('btnVisitante').addEventListener('click', function(e) {
      e.preventDefault();
      window.location.href = 'pagina-visitante.html';
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