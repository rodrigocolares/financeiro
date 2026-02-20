<?php
session_start();

$host = "host";
$db   = "nome_base";
$user = "user_banco";
$pass = "Senha_acesso";

try {
    $con = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
