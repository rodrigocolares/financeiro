<?php
session_start();
include 'layout.php';

// Proteção
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

layout_header("Excluir Conta");
?>

<h2 style="color:red; text-align:center;">Excluir Conta</h2>

<p style="text-align:center;">
    Tem certeza que deseja excluir sua conta?<br>
    <strong>Todos os seus lançamentos serão apagados permanentemente.</strong>
</p>

<form method="POST" action="excluir_conta_confirmar.php" style="text-align:center;">
    <button type="submit" 
            style="background:red; padding:10px 20px; border:none; border-radius:5px;">
        Sim, excluir tudo
    </button>
</form>

<p style="text-align:center; margin-top:20px;">
    <a href="dashboard.php">Cancelar</a>
</p>

<?php layout_footer(); ?>
