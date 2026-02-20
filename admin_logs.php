<?php
include 'conexao.php';
include 'auth_admin.php';
include 'layout.php';

layout_header("Logs de Auditoria");

$stmt = $con->prepare("
    SELECT a.*, u.nome AS usuario_nome
    FROM auditoria a
    LEFT JOIN usuarios u ON u.id = a.usuario_id
    ORDER BY a.data_hora DESC
    LIMIT 500
");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Logs de Auditoria</h2>

<p style="text-align:center;">
    <a href="admin.php">⬅ Voltar</a>
</p>

<table border="1" cellpadding="8" width="100%">
    <tr>
        <th>Data/Hora</th>
        <th>Usuário</th>
        <th>Ação</th>
        <th>Detalhes</th>
        <th>IP</th>
    </tr>

    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log['data_hora'] ?></td>
            <td><?= $log['usuario_nome'] ?: 'Sistema' ?></td>
            <td><?= $log['acao'] ?></td>
            <td><?= $log['detalhes'] ?></td>
            <td><?= $log['ip'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php layout_footer(); ?>
