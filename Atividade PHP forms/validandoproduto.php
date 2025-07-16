<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação Fornecedor</title>
</head>
<body>
<?php
    if(isset($_GET['nomeLivro'])&& isset($_GET['dataPublicacao'])&& isset($_GET['classificacao'])&& isset($_GET['autor'])&& isset($_GET['paginas'])&& isset($_GET['sinopse'])&& isset($_GET['categoria'])){
        echo"Recebido o livro ".$_GET['nomeLivro'];
        echo " publicado no dia ".$_GET['dataPublicacao'];
        echo " classificado como ".$_GET['classificacao'];
        echo " escrito por ".$_GET['autor'];
        echo " com ".$_GET['paginas'] . "paginas ";
        echo " seguido por sua sinopse: ".$_GET['sinopse'];
        echo " sendo sua categoria ".$_GET['categoria'];
    }
?>
<address> 
    Desenvolvimento de Sistemas | Estudante | Marcos Paulo Fernandes
</address>
</body>
</html>