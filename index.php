<?php
include 'conexao.php';
include 'auth.php';

// FILTROS
$where = "WHERE 1=1";
$params = [];

// FILTRO POR CATEGORIA
if (!empty($_GET['categoria'])) {
    $where .= " AND categoria_id = :categoria";
    $params[':categoria'] = $_GET['categoria'];
}

// FILTRO POR FORMA DE PAGAMENTO
if (!empty($_GET['forma'])) {
    $where .= " AND forma_pagamento = :forma";
    $params[':forma'] = $_GET['forma'];
}

// CONSULTA COM JOIN PARA TRAZER O NOME DA CATEGORIA
$sql = "
    SELECT t.*, 
        CASE 
            WHEN t.categoria_id = 1 THEN 'Alimentação'
            WHEN t.categoria_id = 2 THEN 'Transporte'
            WHEN t.categoria_id = 3 THEN 'Moradia'
            WHEN t.categoria_id = 4 THEN 'Lazer'
            WHEN t.categoria_id = 5 THEN 'Salário'
            ELSE 'Não informado'
        END AS categoria_nome
    FROM transacoes t
    $where
    ORDER BY data DESC
";

$stmt = $con->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Controle Financeiro</title>
</head>
<body>

<!-- BOTÃO VOLTAR -->
<a href="dashboard.php" 
   style="display:inline-block; padding:8px 14px; background:#444; color:#fff; 
          text-decoration:none; border-radius:5px; margin-bottom:15px;">
    ⬅ Voltar ao Menu Principal
</a>

<h2>Adicionar Transação</h2>
<form action="adicionar.php" method="POST">
    <label>Tipo:</label>
    <select name="tipo">
        <option value="receita">Receita</option>
        <option value="despesa">Despesa</option>
    </select><br><br>

    <label>Descrição:</label>
    <input type="text" name="descricao" required><br><br>

    <label>Valor:</label>
    <input type="number" step="0.01" name="valor" required><br><br>

    <label>Data:</label>
    <input type="date" name="data" required><br><br>

    <label>Categoria:</label>
    <select name="categoria_id">
        <option value="">Selecione</option>
        <option value="1">Alimentação</option>
        <option value="2">Transporte</option>
        <option value="3">Moradia</option>
        <option value="4">Lazer</option>
        <option value="5">Salário</option>
    </select><br><br>

    <label>Forma de Pagamento:</label>
    <select name="forma_pagamento">
        <option value="dinheiro">💵 Dinheiro</option>
        <option value="cartao">💳 Cartão</option>
    </select><br><br>

    <button type="submit">Salvar</button>
</form>

<hr>

<h2>Filtrar Transações</h2>

<form method="GET">

    <!-- NOVO MENU DE CATEGORIAS -->
    <label>Categoria:</label>
    <select name="categoria">
        <option value="">Todas</option>
        <option value="1">Alimentação</option>
        <option value="2">Transporte</option>
        <option value="3">Moradia</option>
        <option value="4">Lazer</option>
        <option value="5">Salário</option>
    </select>

    <!-- FILTRO DE FORMA DE PAGAMENTO -->
    <label style="margin-left:20px;">Forma de Pagamento:</label>
    <select name="forma">
        <option value="">Todas</option>
        <option value="dinheiro">💵 Dinheiro</option>
        <option value="cartao">💳 Cartão</option>
    </select>

    <button type="submit">Filtrar</button>
</form>

<hr>

<h2>Transações</h2>
<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Descrição</th>
        <th>Valor</th>
        <th>Data</th>
        <th>Categoria</th> <!-- NOVA COLUNA -->
        <th>Forma</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($dados as $linha): ?>
        <tr>
            <td><?= $linha['id'] ?></td>
            <td><?= $linha['tipo'] ?></td>
            <td><?= $linha['descricao'] ?></td>
            <td>R$ <?= number_format($linha['valor'], 2, ',', '.') ?></td>
            <td><?= date('d/m/Y', strtotime($linha['data'])) ?></td>

            <td><?= $linha['categoria_nome'] ?></td> <!-- EXIBE A CATEGORIA -->

            <td>
                <?php if ($linha['forma_pagamento'] == 'dinheiro'): ?>
                    <span style="color:green; font-weight:bold;">💵 Dinheiro</span>
                <?php else: ?>
                    <span style="color:blue; font-weight:bold;">💳 Cartão</span>
                <?php endif; ?>
            </td>

            <td>
                <a href="editar.php?id=<?= $linha['id'] ?>">Editar</a> |
                <a href="excluir.php?id=<?= $linha['id'] ?>"
                   onclick="return confirm('Deseja realmente excluir esta transação?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>

