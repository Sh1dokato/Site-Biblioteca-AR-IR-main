<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meu Perfil - Biblioteca Arco-Íris</title>
  <link rel="stylesheet" href="CSS/perfil.css">
</head>
<body>
  <header class="header">
    <div class="header-title">
      <img src="IMG/logo.png" alt="Logo" style="width: 30px; height: 30px;">
      <span>Biblioteca Arco-Íris</span>
    </div>
    <div class="header-buttons">
      <a href="usuario.php" class="header-btn">Voltar</a>
      <a href="index.php" class="header-btn">Sair</a>
    </div>
  </header>

  <div class="container">
    <div class="profile-card">
      <div class="profile-header">
        <h1>Meu Perfil</h1>
        <p>Gerencie suas informações pessoais</p>
      </div>

      <div class="profile-content">
        <div class="profile-photo-section">
          <div class="photo-container">
            <img id="profilePhoto" src="IMG/default-avatar.svg" alt="Foto do Perfil" class="profile-photo">
            <div class="photo-overlay">
              <label for="photoInput" class="photo-upload-btn">
                <span>📷</span>
                <span>Alterar Foto</span>
              </label>
              <input type="file" id="photoInput" accept="image/*" style="display: none;">
            </div>
          </div>
          <button id="removePhotoBtn" class="remove-photo-btn">Remover Foto</button>
        </div>

        <div class="profile-info">
          <form id="profileForm" class="profile-form">
            <div class="form-group">
              <label for="nome">Nome Completo</label>
              <input type="text" id="nome" name="nome" required>
            </div>

            <div class="form-group">
              <label for="cpf">CPF</label>
              <input type="text" id="cpf" name="cpf" maxlength="14" required>
            </div>

                         <div class="form-group">
               <label for="telefone">Telefone</label>
               <input type="text" id="telefone" name="telefone" maxlength="15" required>
             </div>

            <div class="form-actions">
              <button type="submit" class="btn-save">Salvar Alterações</button>
              <button type="button" class="btn-cancel" onclick="window.location.href='usuario.php'">Cancelar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de confirmação -->
  <div id="confirmModal" class="modal">
    <div class="modal-content">
      <h3>Confirmar Alterações</h3>
      <p>Tem certeza que deseja salvar as alterações no seu perfil?</p>
      <div class="modal-actions">
        <button id="confirmSave" class="btn-confirm">Confirmar</button>
        <button id="cancelSave" class="btn-cancel">Cancelar</button>
      </div>
    </div>
  </div>

  <script>
         // Dados do usuário
     let userProfile = {
       nome: '',
       cpf: '',
       telefone: '',
       foto: null,
       dataCadastro: new Date().toISOString().split('T')[0],
       emprestimos: [],
       favoritos: []
     };

    // Carregar dados do usuário logado
    function carregarUsuarioLogado() {
      const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
      
      if (usuarioLogado) {
        // Carregar dados básicos do usuário logado
        userProfile.nome = usuarioLogado.nome || '';
        userProfile.cpf = usuarioLogado.cpf || '';
        userProfile.telefone = usuarioLogado.telefone || '';
        userProfile.emprestimos = usuarioLogado.loans || [];
        
        // Buscar dados completos do usuário na lista de usuários
        const users = JSON.parse(localStorage.getItem('users')) || [];
        const userComplete = users.find(u => u.id === usuarioLogado.id);
        
        if (userComplete) {
          // Atualizar dados básicos se necessário
          userProfile.nome = userComplete.name || userProfile.nome;
          userProfile.cpf = userComplete.cpf || userProfile.cpf;
          userProfile.telefone = userComplete.phone || userProfile.telefone;
        }
        
        // Se existem dados salvos do perfil, carregar também
        if (localStorage.getItem('userProfile')) {
          const dadosSalvos = JSON.parse(localStorage.getItem('userProfile'));
          userProfile = { ...userProfile, ...dadosSalvos };
        }
      } else {
        // Se não há usuário logado, redirecionar para login
        alert('Usuário não logado!');
        window.location.href = 'login.php';
        return;
      }
    }

    // Elementos do DOM
    const profileForm = document.getElementById('profileForm');
    const photoInput = document.getElementById('photoInput');
    const profilePhoto = document.getElementById('profilePhoto');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    const confirmModal = document.getElementById('confirmModal');
    const confirmSave = document.getElementById('confirmSave');
    const cancelSave = document.getElementById('cancelSave');

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

    

         // Carregar dados no formulário
     function carregarDados() {
       // Primeiro carregar dados do usuário logado
       carregarUsuarioLogado();
       
       // Preencher formulário com os dados
       document.getElementById('nome').value = userProfile.nome || '';
       document.getElementById('cpf').value = userProfile.cpf || '';
       document.getElementById('telefone').value = userProfile.telefone || '';

       // Carregar foto se existir
       if (userProfile.foto) {
         profilePhoto.src = userProfile.foto;
       }

       // Aplicar máscaras
       aplicarMascaraCPF(document.getElementById('cpf'));
       aplicarMascaraTelefone(document.getElementById('telefone'));

       // Calcular estatísticas
       calcularEstatisticas();
     }

    // Calcular estatísticas
    function calcularEstatisticas() {
      const hoje = new Date();
      const dataCadastro = new Date(userProfile.dataCadastro);
      const diasMembro = Math.floor((hoje - dataCadastro) / (1000 * 60 * 60 * 24));

      document.getElementById('totalEmprestimos').textContent = userProfile.emprestimos?.length || 0;
      document.getElementById('emprestimosAtivos').textContent = 
        userProfile.emprestimos?.filter(e => e.status === 'emprestado').length || 0;
      document.getElementById('diasMembro').textContent = diasMembro;
      document.getElementById('livrosFavoritos').textContent = userProfile.favoritos?.length || 0;
    }

    // Upload de foto
    photoInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          profilePhoto.src = e.target.result;
          userProfile.foto = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });

    // Remover foto
    removePhotoBtn.addEventListener('click', function() {
      profilePhoto.src = 'IMG/default-avatar.svg';
      userProfile.foto = null;
    });

         // Aplicar máscaras em tempo real
     document.getElementById('cpf').addEventListener('input', function() {
       aplicarMascaraCPF(this);
     });

     document.getElementById('telefone').addEventListener('input', function() {
       aplicarMascaraTelefone(this);
     });

    // Submissão do formulário
    profileForm.addEventListener('submit', function(e) {
      e.preventDefault();
      confirmModal.style.display = 'flex';
    });

         // Confirmar salvamento
     confirmSave.addEventListener('click', function() {
       // Coletar dados do formulário
       userProfile.nome = document.getElementById('nome').value;
       userProfile.cpf = document.getElementById('cpf').value;
       userProfile.telefone = document.getElementById('telefone').value;

       // Salvar dados do perfil no localStorage
       localStorage.setItem('userProfile', JSON.stringify(userProfile));

       // Atualizar dados do usuário logado
       const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
       if (usuarioLogado) {
         usuarioLogado.nome = userProfile.nome;
         usuarioLogado.cpf = userProfile.cpf;
         usuarioLogado.telefone = userProfile.telefone;
         localStorage.setItem('usuarioLogado', JSON.stringify(usuarioLogado));
       }

       // Atualizar dados na lista de usuários
       const users = JSON.parse(localStorage.getItem('users')) || [];
       const userIndex = users.findIndex(u => u.id === usuarioLogado.id);
       if (userIndex !== -1) {
         users[userIndex].name = userProfile.nome;
         users[userIndex].cpf = userProfile.cpf;
         users[userIndex].phone = userProfile.telefone;
         localStorage.setItem('users', JSON.stringify(users));
       }

       // Fechar modal
       confirmModal.style.display = 'none';

       // Mostrar mensagem de sucesso
       alert('Perfil atualizado com sucesso!');
       
       // Redirecionar para a página do usuário
       window.location.href = 'usuario.php';
     });

    // Cancelar salvamento
    cancelSave.addEventListener('click', function() {
      confirmModal.style.display = 'none';
    });

    // Fechar modal ao clicar fora
    confirmModal.addEventListener('click', function(e) {
      if (e.target === confirmModal) {
        confirmModal.style.display = 'none';
      }
    });

    // Verificar se usuário está logado ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
      const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
      
      if (!usuarioLogado) {
        alert('Você precisa estar logado para acessar esta página!');
        window.location.href = 'login.php';
        return;
      }
      
      // Se está logado, carregar dados
      carregarDados();
    });
  </script>
</body>
</html> 