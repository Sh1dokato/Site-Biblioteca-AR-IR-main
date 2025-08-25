<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro Fornecedor</title>
    <link rel="stylesheet" href="CSS/cadastro_fornecedor.css" />
</head>
<body style="background-image: url(IMG/fundo.png);
             background-size: cover;
             background-position: center;
             background-repeat: no-repeat;
            ">
   <!-- <form id="meuForm" method="post" name="meuForm" action="PHP/cadastrofornecedor.php">
   -->        
   <form id="meuForm" method="post" name="meuForm" action="inicio-admin.php"> 
   <table align="center">
            <tr>
                <td><center><img src="IMG/logo.png" alt="Logo"></center></td>
                <th colspan="3">Cadastro de Fornecedor</th>
            </tr>
            <tr>
                <td><center>Nome:</center></td>
                <td class="a" colspan="3"><input type="text" id="nome" name="nome" /></td>
            </tr>
            <tr>
                <td><center>CEP:</center></td>
                <td><input type="text" id="cep" name="cep" /></td>
                <td>Nº Casa</td>
                <td><input type="text" id="numCasa" name="numCasa" /></td>
            </tr>
            <tr>
                <td><center>CPF/CNPJ:</center></td>
                <td colspan="3"><input type="text" id="cpfCnpj" name="cpfCnpj" /></td>
            </tr>
            <tr>
                <td><center>Telefone:</center></td>
                <td colspan="3"><input type="text" id="telefone" name="telefone" /></td>
            </tr>
            <tr>
                <td colspan="4"><center><a href="php/cadastrofornecedor.php"><button type="submit">Salvar</button></a></center></td>
            </tr>
        </table>
    </form>
    <script type="text/javascript" src="JS/cadastrofornecedor.js"></script>
</body>
</html>