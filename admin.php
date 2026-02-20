<?php
include 'conexao.php';
include 'auth_admin.php';
include 'layout.php';

layout_header("Painel do Administrador");

// Buscar todos os usuários
$stmt = $con->prepare("SELECT id, nome, email, is_admin FROM usuarios ORDER BY nome");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Painel do Administrador</h2>

<p style="text-align:center;">
    <a href="dashboard.php">⬅ Voltar ao Dashboard</a>
</p>

<p style="text-align:center;"> 
 <a href="admin_logs.php">📜 Ver Logs de Auditoria</a> 
</p>

<h3>Usuários cadastrados</h3>

<table border="1" cellpadding="8" width="100%">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Tipo</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= $u['nome'] ?></td>
            <td><?= $u['email'] ?></td>
            <td><?= $u['is_admin'] ? 'Administrador' : 'Usuário' ?></td>
            <td>
                <?php if (!$u['is_admin']): ?>
                    <a href="admin_excluir_usuario.php?id=<?= $u['id'] ?>"
                       onclick="return confirm('Excluir este usuário e todos os lançamentos?')">
                       ❌ Excluir
                    </a>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php layout_footer(); ?>
