<?php
session_start();
include 'conexao.php';
include 'layout.php';
include 'log.php';

layout_header("Login - Controle Financeiro");

$erro = "";
$mensagem = "";

// MOSTRA MENSAGEM DE CONTA EXCLUÍDA
if (isset($_GET['msg']) && $_GET['msg'] === 'conta_excluida') {
    $mensagem = "Sua conta foi excluída com sucesso.";
}

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    $stmt = $con->prepare("SELECT id, nome, senha, nivel, is_admin FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       if (password_verify($senha, $user['senha'])) { 
	   
	   $_SESSION['usuario_id'] = $user['id']; 
	   $_SESSION['usuario_nome'] = $user['nome']; 
	   $_SESSION['is_admin'] = $user['is_admin']; // ← AQUI ENTRA 
	   $_SESSION['nivel'] = $user['nivel'];

	   
	   // 🔥 AUDITORIA: LOGIN OK 
	   registrar_log($con, $user['id'], "Login realizado");
	   
	   header ("Location: dashboard.php"); 
	   exit;
	   }
    }
	
	// 🔥 AUDITORIA: LOGIN FALHOU 
	registrar_log($con, null, "Tentativa de login falhou", "Email: $email");

    $erro = "Usuário ou senha inválidos.";
}

if ($erro) echo "<p class='msg-erro'>$erro</p>";
if ($mensagem) echo "<p class='msg-sucesso'>$mensagem</p>";
?>

<h2> Controle Financeiro </h2>

<form method="POST">
    <input type="hidden" name="login" value="1">

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Senha:</label>
    <input type="password" name="senha" required>

    <button type="submit">Entrar</button>
</form>

<p style="text-align:center; margin-top:15px;">
    <a href="esqueci_senha.php">Esqueci a senha</a>
</p>

<p style="text-align:center;">
    <a href="registrar.php">Criar conta</a>
</p>

<!-- NOVA OPÇÃO: EXCLUIR CONTA
<p style="text-align:center; margin-top:20px;">
    <a href="excluir_conta.php" style="color:red; font-weight:bold;">
        ❌ Excluir minha conta
    </a>
</p>
-->

<?php layout_footer(); ?>
