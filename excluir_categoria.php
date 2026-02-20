<?php
include 'conexao.php';
include 'auth.php';

$id = $_GET['id'];

$stmt = $con->prepare("DELETE FROM categorias WHERE id=:id");
$stmt->execute([':id' => $id]);

header("Location: categorias.php");
exit;
?>

