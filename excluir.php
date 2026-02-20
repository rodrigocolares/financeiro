<?php
include 'conexao.php';
include 'auth.php';
include 'log.php';

registrar_log($con, $_SESSION['usuario_id'], "Transação excluída", "ID: $id");


$id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

$stmt = $con->prepare("DELETE FROM transacoes WHERE id=:id AND usuario_id=:usuario_id");
$stmt->execute([':id' => $id, ':usuario_id' => $usuario_id]);

header("Location: index.php");
exit;
?>

