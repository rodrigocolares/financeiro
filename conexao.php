<?php
session_start();

$host = "financeirocon.mysql.dbaas.com.br";
$db   = "financeirocon";
$user = "financeirocon";
$pass = "Suplwrev@09";

try {
    $con = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>