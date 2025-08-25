<?php
require_once 'PHP/database.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Meus Empréstimos - Biblioteca Arco-Íris</title>
  <link rel="icon" href="favicon.ico">
  <link rel="stylesheet" href="CSS/emprestimo.css">
</head>
<body>
  <header class="header">
    <div class="header-title">
      <img src="IMG/logo.png" alt="Logo" style="height: 30px;">
      <span>Biblioteca Arco-Íris</span>
    </div>
    <div class="header-buttons">
      <a href="usuario.php" class="header-btn">Voltar</a>
      <a href="index.php" class="header-btn">Sair</a>
    </div>
  </header>
  <div class="emprestimos-container" id="emprestimosContainer">
    <!-- Empréstimos serão carregados via JS -->
  </div>

  <!-- Modal de Pagamento de Multa -->
  <div id="modalMulta" class="modal">
    <div class="modal-content">
      <span class="close-modal" id="closeModalMulta">&times;</span>
      <h2 class="modal-title">Pagamento de Multa</h2>
      <div id="multaInfo"></div>
      <div class="payment-options">
        <div class="payment-option" data-method="pix">
          <img src="IMG/pix.png" alt="Pix">
          <span>Pix</span>
        </div>
        <div class="payment-option" data-method="boleto">
          <img src="IMG/boleto.png" alt="Boleto">
          <span>Boleto</span>
        </div>
        <div class="payment-option" data-method="card">
          <img src="IMG/card.png" alt="Cartão">
          <span>Cartão</span>
        </div>
        <div class="payment-option" data-method="doacao">
          <img src="IMG/doacao.png" alt="Doação">
          <span>Doação</span>
        </div>
      </div>
      <button class="btn-confirmar" id="confirmarPagamento">Confirmar Pagamento</button>
      <button class="btn-cancelar" id="cancelarPagamento">Cancelar</button>
      <div class="qrcode-container" id="qrcodeContainer" style="display:none;">
        <h3>Escaneie o QR Code</h3>
        <img src="IMG/qrcode.png" alt="QR Code" class="qrcode-image">
        <p>Após o pagamento, clique em "Confirmar Pagamento".</p>
        <button class="btn-close-qrcode" id="closeQrcode">Fechar</button>
      </div>
    </div>
  </div>

  <script>
    // Verificar se o usuário está logado
    window.addEventListener('load', function() {
      const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
      if (!usuarioLogado) {
        window.location.href = 'login.php';
        return;
      }
      carregarEmprestimos();
    });

    // Listener para quando uma doação for confirmada pelo admin
    window.addEventListener('doacaoConfirmada', function(event) {
      const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
      if (usuarioLogado && usuarioLogado.id === event.detail.userId) {
        // Recarregar empréstimos se for o usuário afetado
        carregarEmprestimos();
      }
    });

    // Listener para quando a página ganhar foco (usuário voltar para a aba)
    window.addEventListener('focus', function() {
      carregarEmprestimos();
    });

    function carregarEmprestimos() {
      const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
      const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
      const container = document.getElementById('emprestimosContainer');
      // Mostrar apenas empréstimos ativos e aguardando aprovação de devolução
      const meusEmprestimos = emprestimos.filter(e => e.userId === usuarioLogado.id && !e.returned && e.status !== 'devolvido');
      if (meusEmprestimos.length === 0) {
        container.innerHTML = `<div class="empty-state"><h2>Você não possui empréstimos ativos.</h2></div>`;
        return;
      }
      container.innerHTML = '';
      meusEmprestimos.forEach(emprestimo => {
        const statusClass = `status ${emprestimo.status}`;
        const multa = emprestimo.debtAmount && emprestimo.debtAmount > 0 ? `<div class='multa-info'><p>Multa pendente: R$ ${emprestimo.debtAmount.toFixed(2)}</p><button class='btn-pagar-multa' onclick='abrirModalMulta(${emprestimo.id})'>Pagar Multa</button></div>` : '';
        const doacaoPendente = usuarioLogado.hasPendingDonation ? `<div class='doacao-pendente'><p>⚠️ Você tem uma doação de item de higiene pendente de aprovação</p></div>` : '';
        // Se já está aguardando aprovação, desabilita botão
        const aguardando = emprestimo.status === 'aguardando_devolucao';
        const btnDevolver = `<button class='devolver-btn${emprestimo.debtAmount > 0 || aguardando ? ' pendente' : ''}' ${emprestimo.debtAmount > 0 || aguardando ? 'disabled' : ''} onclick='devolverEmprestimo(${emprestimo.id})'>${aguardando ? 'Aguardando aprovação' : 'Devolver'}</button>`;
        // Verificar se já se passaram 7 dias desde o empréstimo
        const dataEmprestimo = new Date(emprestimo.loanDate || emprestimo.dataEmprestimo);
        const hoje = new Date();
        const diasPassados = Math.floor((hoje - dataEmprestimo) / (1000 * 60 * 60 * 24));
        const podeRenovar = emprestimo.status === 'emprestado' && !emprestimo.renovado && diasPassados >= 6;
        
        const btnRenovar = podeRenovar ? `<button class='btn-renovar' onclick='renovarEmprestimo(${emprestimo.id})'>Renovar</button>` : '';
        
        // Informação sobre quando poderá renovar
        let infoRenovacao = '';
        if (!podeRenovar && emprestimo.status === 'emprestado' && !emprestimo.renovado) {
          if (diasPassados < 6) {
            const diasRestantes = 6 - diasPassados;
            infoRenovacao = `<p class='info-renovacao'>Renovação disponível em ${diasRestantes} ${diasRestantes === 1 ? 'dia' : 'dias'}</p>`;
          }
        } else if (emprestimo.renovado) {
          infoRenovacao = `<p class='info-renovacao renovado'>Empréstimo já renovado</p>`;
        }
        container.innerHTML += `
          <div class='emprestimo-card${emprestimo.status === 'atrasado' ? ' atrasado' : ''}'>
            <img src='${emprestimo.bookImage || emprestimo.imagem}' alt='${emprestimo.bookTitle || emprestimo.titulo}' class='book-image'>
            <div class='emprestimo-info'>
              <h3>${emprestimo.bookTitle || emprestimo.titulo}</h3>
              <p><span class='status ${emprestimo.status}'>${emprestimo.status === 'aguardando_devolucao' ? 'Aguardando aprovação do admin' : (emprestimo.status.charAt(0).toUpperCase() + emprestimo.status.slice(1))}</span></p>
              <p>Data do Empréstimo: ${emprestimo.dataEmprestimo || new Date(emprestimo.loanDate).toLocaleDateString('pt-BR')}</p>
              <p>Data de Devolução: ${emprestimo.dataDevolucao || new Date(emprestimo.dueDate).toLocaleDateString('pt-BR')}</p>
              ${multa}
              ${doacaoPendente}
            </div>
            <div class='emprestimo-actions'>
              ${btnRenovar}
              ${infoRenovacao}
              ${btnDevolver}
            </div>
          </div>
        `;
      });
    }

    function renovarEmprestimo(id) {
      const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
      const emprestimo = emprestimos.find(e => e.id === id);
      if (!emprestimo || emprestimo.renovado) return;
      // Adicionar 7 dias à data de devolução
      const novaData = new Date(emprestimo.dueDate || new Date());
      novaData.setDate(novaData.getDate() + 7);
      emprestimo.dueDate = novaData.toISOString();
      emprestimo.dataDevolucao = novaData.toLocaleDateString('pt-BR');
      emprestimo.renovado = true;
      localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
      carregarEmprestimos();
      alert('Empréstimo renovado por mais 7 dias!');
    }

    // Modal de pagamento de multa
    let emprestimoMultaId = null;
    function abrirModalMulta(id) {
      emprestimoMultaId = id;
      const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
      const emprestimo = emprestimos.find(e => e.id === id);
      if (!emprestimo) return;
      document.getElementById('modalMulta').style.display = 'block';
      document.getElementById('multaInfo').innerHTML = `<p>Valor da multa: <b>R$ ${emprestimo.debtAmount.toFixed(2)}</b></p>`;
      document.getElementById('qrcodeContainer').style.display = 'none';
      
      // Remover classe pix-selected ao abrir o modal
      const modalContent = document.querySelector('.modal-content');
      modalContent.classList.remove('pix-selected');
    }
    window.abrirModalMulta = abrirModalMulta;

    document.getElementById('closeModalMulta').onclick = function() {
      document.getElementById('modalMulta').style.display = 'none';
      const modalContent = document.querySelector('.modal-content');
      modalContent.classList.remove('pix-selected');
    };
    document.getElementById('cancelarPagamento').onclick = function() {
      document.getElementById('modalMulta').style.display = 'none';
      const modalContent = document.querySelector('.modal-content');
      modalContent.classList.remove('pix-selected');
    };
    document.getElementById('closeQrcode').onclick = function() {
      document.getElementById('qrcodeContainer').style.display = 'none';
    };

    // Seleção de método de pagamento
    document.querySelectorAll('.payment-option').forEach(opt => {
      opt.onclick = function() {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        
        const modalContent = document.querySelector('.modal-content');
        
        if (this.dataset.method === 'pix') {
          document.getElementById('qrcodeContainer').style.display = 'block';
          modalContent.classList.add('pix-selected');
        } else {
          document.getElementById('qrcodeContainer').style.display = 'none';
          modalContent.classList.remove('pix-selected');
        }
      };
    });

    document.getElementById('confirmarPagamento').onclick = function() {
      if (!emprestimoMultaId) return;
      
      const selectedMethod = document.querySelector('.payment-option.selected');
      if (!selectedMethod) {
        alert('Por favor, selecione um método de pagamento.');
        return;
      }

      const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
      const emprestimo = emprestimos.find(e => e.id === emprestimoMultaId);
      if (!emprestimo) return;

      if (selectedMethod.dataset.method === 'doacao') {
        // Processar doação
        const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
        const pendingDonations = JSON.parse(localStorage.getItem('pendingDonations') || '[]');
        
        // Criar nova doação pendente
        const novaDoacao = {
          id: Date.now(),
          userId: usuarioLogado.id,
          userName: usuarioLogado.nome,
          bookTitle: emprestimo.bookTitle || emprestimo.titulo,
          bookImage: emprestimo.bookImage || emprestimo.imagem,
          debtAmount: emprestimo.debtAmount,
          date: new Date().toISOString().split('T')[0],
          status: 'pendente'
        };
        
        pendingDonations.push(novaDoacao);
        localStorage.setItem('pendingDonations', JSON.stringify(pendingDonations));
        
        // Marcar usuário como tendo doação pendente
        const users = JSON.parse(localStorage.getItem('users') || '[]');
        const userIndex = users.findIndex(u => u.id === usuarioLogado.id);
        if (userIndex !== -1) {
          users[userIndex].hasPendingDonation = true;
          localStorage.setItem('users', JSON.stringify(users));
        }
        
        // Atualizar usuário logado
        usuarioLogado.hasPendingDonation = true;
        localStorage.setItem('usuarioLogado', JSON.stringify(usuarioLogado));
        
        document.getElementById('modalMulta').style.display = 'none';
        carregarEmprestimos();
        alert('Solicitação de doação de item de higiene enviada! Aguarde aprovação do administrador.');
      } else {
        // Processar pagamento normal
        emprestimo.debtAmount = 0;
        localStorage.setItem('emprestimos', JSON.stringify(emprestimos));
        document.getElementById('modalMulta').style.display = 'none';
        carregarEmprestimos();
        alert('Multa paga com sucesso!');
      }
    };

    // Função para solicitar devolução
    window.devolverEmprestimo = function(id) {
      const emprestimos = JSON.parse(localStorage.getItem('emprestimos') || '[]');
      const emprestimo = emprestimos.find(e => e.id === id);
      if (!emprestimo) return;
      if (emprestimo.status === 'aguardando_devolucao') return;
      emprestimo.status = 'aguardando_devolucao';
      localStorage.setItem('emprestimos', JSON.stringify(emprestimos));

      // Adicionar solicitação de devolução para o admin
      const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
      let solicitacoes = JSON.parse(localStorage.getItem('solicitacoesDevolucao') || '[]');
      solicitacoes.push({
        id: Date.now(),
        emprestimoId: emprestimo.id,
        userId: usuarioLogado.id,
        titulo: emprestimo.bookTitle || emprestimo.titulo,
        imagem: emprestimo.bookImage || emprestimo.imagem,
        dataEmprestimo: emprestimo.dataEmprestimo || emprestimo.loanDate,
        userName: usuarioLogado.nome,
        userPhone: usuarioLogado.telefone
      });
      localStorage.setItem('solicitacoesDevolucao', JSON.stringify(solicitacoes));

      carregarEmprestimos();
      alert('Solicitação de devolução enviada. Aguarde aprovação do administrador.');
    };

    // Fechar modal ao clicar fora
    window.onclick = function(event) {
      const modal = document.getElementById('modalMulta');
      if (event.target === modal) {
        modal.style.display = 'none';
        const modalContent = document.querySelector('.modal-content');
        modalContent.classList.remove('pix-selected');
      }
    };
  </script>
</body>
</html> 