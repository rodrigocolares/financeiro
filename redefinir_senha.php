<?php
include 'conexao.php';
include 'layout.php';

layout_header("Redefinir Senha");

$erro = "";
$mensagem = "";
$token = $_GET['token'] ?? '';

if (!$token) {
    $erro = "Token inválido.";
} else {
    $stmt = $con->prepare("
        SELECT id
          FROM usuarios
         WHERE reset_token = :token
           AND reset_token_expira >= NOW()
         LIMIT 1
    ");
    $stmt->bindValue(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        $erro = "Token inválido ou expirado.";
    } else {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senha = $_POST['senha'];
            $senha2 = $_POST['senha2'];

            if ($senha !== $senha2) {
                $erro = "As senhas não conferem.";
            } elseif (strlen($senha) < 6) {
                $erro = "A senha deve ter pelo menos 6 caracteres.";
            } else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                $stmtUp = $con->prepare("
                    UPDATE usuarios
                       SET senha = :senha,
                           reset_token = NULL,
                           reset_token_expira = NULL
                     WHERE id = :id
                ");
                $stmtUp->bindValue(':senha', $hash);
                $stmtUp->bindValue(':id', $user['id']);

                if ($stmtUp->execute()) {
                    $mensagem = "Senha redefinida com sucesso!";
                } else {
                    $erro = "Erro ao redefinir senha.";
                }
            }
        }
    }
}

if ($erro) echo "<p class='msg-erro'>$erro</p>";
if ($mensagem) echo "<p class='msg-sucesso'>$mensagem</p>";
?>

<?php if (!$mensagem && !$erro): ?>
<h2>Redefinir Senha</h2>

<form method="POST">
    <label>Nova senha:</label>
    <input type="password" name="senha" required>

    <label>Repita a nova senha:</label>
    <input type="password" name="senha2" required>

    <button type="submit">Redefinir senha</button>
</form>
<?php endif; ?>

<p style="text-align:center; margin-top:15px;">
    <a href="login.php">Voltar ao login</a>
</p>

<?php layout_footer(); ?>
