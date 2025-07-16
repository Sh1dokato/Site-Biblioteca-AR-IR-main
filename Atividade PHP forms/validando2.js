document.addEventListener("DOMContentLoaded", function () {
    var formEl = document.getElementById("meuForm");

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
        var nomeLivro = formEl.nomeLivro.value.trim();
        var dataPublicacao = formEl.dataPublicacao.value.trim();
        var classificacao = formEl.classificacao.value.trim();
        var autor = formEl.autor.value.trim();
        var paginas = formEl.paginas.value.trim();
        var sinopse = formEl.sinopse.value.trim();
        var categoria = formEl.categoria.value.trim();

        if (nomeLivro.length === 0) {
            alert("O campo Nome do Livro é obrigatório.");
            cancela_evento(event);
            formEl.nomeLivro.focus();
            return false;
        }

        if (dataPublicacao.length === 0) {
            alert("O campo Data de Publicação é obrigatório.");
            cancela_evento(event);
            formEl.dataPublicacao.focus();
            return false;
        }

        if (classificacao.length === 0) {
            alert("O campo Classificação é obrigatório.");
            cancela_evento(event);
            formEl.classificacao.focus();
            return false;
        }

        if (autor.length === 0) {
            alert("O campo Autor é obrigatório.");
            cancela_evento(event);
            formEl.autor.focus();
            return false;
        }

        if (paginas.length === 0) {
            alert("O campo Nº de Páginas é obrigatório.");
            cancela_evento(event);
            formEl.paginas.focus();
            return false;
        }

        if (sinopse.length === 0) {
            alert("O campo Sinopse é obrigatório.");
            cancela_evento(event);
            formEl.sinopse.focus();
            return false;
        }

        if (categoria.length === 0) {
            alert("O campo Categoria é obrigatório.");
            cancela_evento(event);
            formEl.categoria.focus();
            return false;
        }

        alert("Formulário válido e será enviado!");
        return true;
    }
});
