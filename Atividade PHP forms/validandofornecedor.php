<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação Fornecedor</title>
</head>
<body>
<?php
    if(isset($_GET['nome'])&& isset($_GET['cep'])&& isset($_GET['numCasa'])&& isset($_GET['cpfCnpj'])&& isset($_GET['telefone'])){
        echo"Recebido fornecedor ".$_GET['nome'];
        echo " morador do CEP ".$_GET['cep'];
        echo " com a casa numero ".$_GET['numCasa'];
        echo " detentor do CPF/CNPJ ".$_GET['cpfCnpj'];
        echo " Sendo seu numero de telefone: ".$_GET['telefone'];
    }
?>
<address> 
    Desenvolvimento de Sistemas | Estudante | Marcos Paulo Fernandes
</address>
</body>
</html>