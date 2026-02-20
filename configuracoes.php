<?php
session_start();
include 'conexao.php';
include 'layout.php';

// Proteção de sessão
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$nome = $_SESSION['usuario_nome'];

layout_header("Configurações da Conta");
?>

<h2>Configurações da Conta</h2>

<p style="text-align:center;">
    Olá, <strong><?= htmlspecialchars($nome) ?></strong>
</p>

<hr>

<h3 style="color:red; text-align:center;">Excluir Conta</h3>

<p style="text-align:center;">
    Esta ação é permanente e irá excluir:
</p>

<ul style="max-width:300px; margin:0 auto;">
    <li>Seu usuário</li>
    <li>Todos os seus lançamentos</li>
    <li>Todos os seus dados associados</li>
</ul>

<p style="text-align:center; margin-top:20px;">
    <a href="excluir_conta.php"
       style="background:red; color:#fff; padding:10px 20px; 
              border-radius:5px; text-decoration:none;">
        ❌ Excluir minha conta
    </a>
</p>

<p style="text-align:center; margin-top:20px;">
    <a href="dashboard.php">⬅ Voltar ao Dashboard</a>
</p>

<?php layout_footer(); ?>
