<?php
include 'conexao.php';
include 'auth_admin.php';
include 'log.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: admin.php");
    exit;
}

// Buscar dados do usuário que será excluído 
$stmtUser = $con->prepare("SELECT nome, email FROM usuarios WHERE id = :id LIMIT 1"); 
$stmtUser->bindValue(':id', $id); 
$stmtUser->execute(); 
$u = $stmtUser->fetch(PDO::FETCH_ASSOC); 

// 🔥 AUDITORIA: ADMIN EXCLUIU USUÁRIO 
registrar_log(
 $con, 
 $_SESSION['usuario_id'], 
 "Admin excluiu usuário", 
 "ID: $id | Nome: {$u['nome']} | Email: {$u['email']}"
 );

// Excluir transações
$stmt1 = $con->prepare("DELETE FROM transacoes WHERE usuario_id = :id");
$stmt1->bindValue(':id', $id);
$stmt1->execute();

// Excluir usuário
$stmt2 = $con->prepare("DELETE FROM usuarios WHERE id = :id AND is_admin = 0");
$stmt2->bindValue(':id', $id);
$stmt2->execute();

header("Location: admin.php?msg=usuario_excluido");
exit;
