var formEl = document.getElementById("meuForm");

// Função para adicionar evento de submit
captura_eventos(formEl, 'submit', validaForm);

function captura_eventos(objeto, evento, funcao) {
    if (objeto.addEventListener) {
        objeto.addEventListener(evento, funcao, false);
    } else if (objeto.attachEvent) {
        objeto.attachEvent('on' + evento, funcao);
    }
}

function cancela_evento(event) {
    if (event.preventDefault) {
        event.preventDefault();
    } else {
        window.event.returnValue = false;
    }
}

function validaForm(event) {
    // Pega os valores dos campos pelo name
    var nome = formEl.nome.value.trim();
    var cep = formEl.cep.value.trim();
    var numCasa = formEl.numCasa.value.trim();
    var cpfCnpj = formEl.cpfCnpj.value.trim();
    var telefone = formEl.telefone.value.trim();

    if (nome.length === 0) {
        alert("O campo Nome é obrigatório.");
        cancela_evento(event);
        formEl.nome.focus();
        return false;
    }

    if (cep.length === 0) {
        alert("O campo CEP é obrigatório.");
        cancela_evento(event);
        formEl.cep.focus();
        return false;
    }

    if (numCasa.length === 0) {
        alert("O campo Nº Casa é obrigatório.");
        cancela_evento(event);
        formEl.numCasa.focus();
        return false;
    }

    if (cpfCnpj.length === 0) {
        alert("O campo CPF/CNPJ é obrigatório.");
        cancela_evento(event);
        formEl.cpfCnpj.focus();
        return false;
    }

    if (telefone.length === 0) {
        alert("O campo Telefone é obrigatório.");
        cancela_evento(event);
        formEl.telefone.focus();
        return false;
    }

    alert("Formulário válido e será enviado!");
    // Aqui o envio acontece normalmente
    return true;
}