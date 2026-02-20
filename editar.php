<?php
include 'conexao.php';
include 'auth.php';
include 'header.php';
include 'log.php';

registrar_log($con, $_SESSION['usuario_id'], "Transação editada", "ID: $id");

$id = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

/* Buscar transação */
$stmt = $con->prepare("
    SELECT * FROM transacoes 
    WHERE id = :id AND usuario_id = :usuario_id
");
$stmt->execute([':id' => $id, ':usuario_id' => $usuario_id]);
$transacao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transacao) {
    echo "<div class='alert alert-danger'>Transação não encontrada.</div>";
    include 'footer.php';
    exit;
}

/* Categorias */
$stmtCat = $con->prepare("SELECT * FROM categorias ORDER BY nome");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

/* Atualizar */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $con->prepare("
        UPDATE transacoes SET tipo=:tipo, descricao=:descricao, valor=:valor, data=:data, categoria_id=:categoria_id
        WHERE id=:id AND usuario_id=:usuario_id
    ");

    $stmt->execute([
        ':tipo' => $_POST['tipo'],
        ':descricao' => $_POST['descricao'],
        ':valor' => $_POST['valor'],
        ':data' => $_POST['data'],
        ':categoria_id' => $_POST['categoria_id'] ?: null,
        ':id' => $id,
        ':usuario_id' => $usuario_id
    ]);

    header("Location: index.php");
    exit;
}
?>

<h2>Editar Transação</h2>

<form method="POST" class="card p-4 shadow-sm">
    <label class="form-label">Tipo</label>
    <select name="tipo" class="form-select">
        <option value="receita" <?= $transacao['tipo']=='receita'?'selected':'' ?>>Receita</option>
        <option value="despesa" <?= $transacao['tipo']=='despesa'?'selected':'' ?>>Despesa</option>
    </select>

    <label class="form-label mt-3">Descrição</label>
    <input type="text" name="descricao" class="form-control" value="<?= $transacao['descricao'] ?>">

    <label class="form-label mt-3">Valor</label>
    <input type="number" step="0.01" name="valor" class="form-control" value="<?= $transacao['valor'] ?>">

    <label class="form-label mt-3">Data</label>
    <input type="date" name="data" class="form-control" value="<?= $transacao['data'] ?>">

    <label class="form-label mt-3">Categoria</label>
    <select name="categoria_id" class="form-select">
        <option value="">-- Selecione --</option>
        <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id']==$transacao['categoria_id']?'selected':'' ?>>
                <?= $cat['nome'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="btn btn-primary mt-4">Salvar</button>
</form>

<?php include 'footer.php'; ?>

