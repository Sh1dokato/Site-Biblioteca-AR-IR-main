<?php
function conectarBanco(){
$nomeBd = "mysql:host=localhost;dbname=biblioteca_arco_iris;charset=utf8mb4";
$usuario = "root";
$senha = "";
try {
    $conexao = new PDO($nomeBd, $usuario, $senha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $conexao;
} catch (PDOException $e) {
    error_log("Erro ao conectar ao banco" . $e->getMessage());
    //LOG SEM EXPOR ERRO AO USUARIO
    die("Erro ao conectar no banco.");
}
}
?>