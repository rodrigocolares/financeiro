<?php
include 'conexao.php';
include 'auth.php';
include 'header.php';

/* Adicionar categoria */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $con->prepare("INSERT INTO categorias (nome) VALUES (:nome)");
    $stmt->execute([':nome' => $_POST['nome']]);
}

/* Listar */
$stmt = $con->query("SELECT * FROM categorias ORDER BY nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Categorias</h2>

<form method="POST" class="card p-4 shadow-sm mb-4">
    <label class="form-label">Nova categoria</label>
    <input type="text" name="nome" class="form-control" required>
    <button class="btn btn-success mt-3">Adicionar</button>
</form>

<table class="table table-striped table-bordered shadow-sm">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($categorias as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= $cat['nome'] ?></td>
            <td>
                <a href="excluir_categoria.php?id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>

