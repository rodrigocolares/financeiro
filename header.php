<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Financeiro</a>

        <div>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <span class="text-white me-3">Olá, <?= $_SESSION['usuario_nome']; ?></span>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="categorias.php" class="btn btn-outline-light btn-sm me-2">Categorias</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">

