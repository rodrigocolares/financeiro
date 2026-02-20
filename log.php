<?php
function registrar_log($con, $usuario_id, $acao, $detalhes = null) {

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido';

    $sql = "INSERT INTO auditoria (usuario_id, acao, detalhes, ip, user_agent)
            VALUES (:usuario_id, :acao, :detalhes, :ip, :user_agent)";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':usuario_id', $usuario_id);
    $stmt->bindValue(':acao', $acao);
    $stmt->bindValue(':detalhes', $detalhes);
    $stmt->bindValue(':ip', $ip);
    $stmt->bindValue(':user_agent', $user_agent);
    $stmt->execute();
}
