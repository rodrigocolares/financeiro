<?php
include 'conexao.php';
include 'layout.php';

require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

layout_header("Recuperar Senha");

$erro = "";
$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $stmt = $con->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', time() + 3600);

        $stmtUp = $con->prepare("
            UPDATE usuarios
               SET reset_token = :token,
                   reset_token_expira = :expira
             WHERE id = :id
        ");
        $stmtUp->bindValue(':token', $token);
        $stmtUp->bindValue(':expira', $expira);
        $stmtUp->bindValue(':id', $user['id']);
        $stmtUp->execute();

        $link = "https://controlefina.com.br/redefinir_senha.php?token=" . urlencode($token);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'host';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'SUA_Conta';
            $mail->Password   = 'SUA_Senha';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('admin@controlefina.com.br', 'Controle Financeiro');
            $mail->addAddress($email);

            $mail->Subject = 'Redefinição de Senha';
            $mail->Body    = "Clique no link abaixo para redefinir sua senha:\n\n$link\n\nVálido por 1 hora.";

            $mail->send();
            $mensagem = "Se o e-mail existir, enviamos um link de redefinição.";
        } catch (Exception $e) {
            $erro = "Erro ao enviar e-mail: " . $mail->ErrorInfo;
        }

    } else {
        $mensagem = "Se o e-mail existir, enviamos um link de redefinição.";
    }
}

if ($erro) echo "<p class='msg-erro'>$erro</p>";
if ($mensagem) echo "<p class='msg-sucesso'>$mensagem</p>";
?>

<h2>Recuperar Senha</h2>

<form method="POST">
    <label>Informe seu e-mail:</label>
    <input type="email" name="email" required>

    <button type="submit">Enviar link</button>
</form>

<p style="text-align:center; margin-top:15px;">
    <a href="login.php">Voltar ao login</a>
</p>

<?php layout_footer(); ?>
