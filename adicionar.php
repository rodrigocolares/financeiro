<?php
include 'conexao.php';
include 'auth.php';
include 'log.php';

registrar_log($con, $_SESSION['usuario_id'], "Nova transação", "Descrição: $descricao, Valor: $valor");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo            = $_POST['tipo'];
    $descricao       = $_POST['descricao'];
    $valor           = $_POST['valor'];
    $data            = $_POST['data'];
    $categoria_id    = !empty($_POST['categoria_id']) ? $_POST['categoria_id'] : null;
    $forma_pagamento = $_POST['forma_pagamento']; // <-- NOVO CAMPO
    $usuario_id      = $_SESSION['usuario_id'];

    $stmt = $con->prepare("
        INSERT INTO transacoes 
            (tipo, descricao, valor, data, categoria_id, usuario_id, forma_pagamento)
        VALUES 
            (:tipo, :descricao, :valor, :data, :categoria_id, :usuario_id, :forma_pagamento)
    ");

    $stmt->bindValue(':tipo', $tipo);
    $stmt->bindValue(':descricao', $descricao);
    $stmt->bindValue(':valor', $valor);
    $stmt->bindValue(':data', $data);
    $stmt->bindValue(':categoria_id', $categoria_id, $categoria_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->bindValue(':forma_pagamento', $forma_pagamento);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        echo "Erro ao salvar transação.";
    }
}
?>

