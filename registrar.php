<?php
include 'conexao.php';
include 'layout.php';

layout_header("Criar Conta");

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $con->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $erro = "Já existe um usuário com esse e-mail.";
    } else {
        $stmt = $con->prepare("
            INSERT INTO usuarios (nome, email, senha)
            VALUES (:nome, :email, :senha)
        ");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':senha', $hash);

        if ($stmt->execute()) {
            $sucesso = "Usuário cadastrado com sucesso! Você já pode fazer login.";
        } else {
            $erro = "Erro ao cadastrar usuário.";
        }
    }
}

if ($erro) echo "<p class='msg-erro'>$erro</p>";
if ($sucesso) echo "<p class='msg-sucesso'>$sucesso</p>";
?>

<h2>Criar Conta</h2>

<form method="POST">
    <label>Nome:</label>
    <input type="text" name="nome" required>

    <label>E-mail:</label>
    <input type="email" name="email" required>

    <label>Senha:</label>
    <input type="password" name="senha" required>

    <button type="submit">Cadastrar</button>
</form>

<p style="text-align:center; margin-top:15px;">
    <a href="login.php">Voltar ao login</a>
</p>

<?php layout_footer(); ?>
