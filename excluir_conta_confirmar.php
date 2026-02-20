<?php
session_start();
require 'conexao.php';
require 'log.php'; // ← IMPORTANTE: adicionar o arquivo de auditoria

// Carrega PHPMailer
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Proteção
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar dados do usuário antes de excluir
$stmtUser = $con->prepare("SELECT nome, email FROM usuarios WHERE id = :id LIMIT 1");
$stmtUser->bindValue(':id', $usuario_id);
$stmtUser->execute();
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$nome  = $user['nome'];
$email = $user['email'];

// 🔥 AUDITORIA: REGISTRA EXCLUSÃO DA PRÓPRIA CONTA 

registrar_log($con, $usuario_id, "Usuário excluiu a própria conta", "Nome: $nome, Email: $email");

// 1. Excluir transações
$stmt1 = $con->prepare("DELETE FROM transacoes WHERE usuario_id = :id");
$stmt1->bindValue(':id', $usuario_id);
$stmt1->execute();

// 2. Excluir usuário
$stmt2 = $con->prepare("DELETE FROM usuarios WHERE id = :id");
$stmt2->bindValue(':id', $usuario_id);
$stmt2->execute();

// 3. Enviar e-mail de confirmação
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'host';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'SUA_Conta';
    $mail->Password   = 'SUA_Senha';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Permitir certificado SSL autoassinado
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom('admin@controlefina.com.br', 'Controle Financeiro');
    $mail->addAddress($email, $nome);

    $mail->Subject = 'Confirmação de Exclusão de Conta';
    $mail->Body = "
Olá, $nome.

Sua conta no sistema Controle Financeiro foi excluída com sucesso.

Todos os seus lançamentos e dados associados foram removidos permanentemente.

Se esta ação não foi realizada por você, entre em contato imediatamente.

Atenciosamente,
Equipe Controle Financeiro
";

    $mail->send();

} catch (Exception $e) {
    // Não interrompe o processo caso o e-mail falhe
}

// 4. Encerrar sessão
session_unset();
session_destroy();

// 5. Redirecionar
header("Location: login.php?msg=conta_excluida");
exit;
