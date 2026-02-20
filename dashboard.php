<?php
session_start();
include 'conexao.php';
include 'layout.php';

// Proteção de sessão
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';

/* ============================
   FILTROS DE PERÍODO
============================ */
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim    = $_GET['data_fim'] ?? '';

$wherePeriodo = "";
$params = [':usuario_id' => $usuario_id];

if ($data_inicio && $data_fim) {
    $wherePeriodo = " AND t.data BETWEEN :data_inicio AND :data_fim ";
    $params[':data_inicio'] = $data_inicio;
    $params[':data_fim']    = $data_fim;
}

/* ============================
   TOTAL DE RECEITAS E DESPESAS
============================ */
$sqlTotais = "
    SELECT t.tipo, SUM(t.valor) AS total
    FROM transacoes t
    WHERE t.usuario_id = :usuario_id
    $wherePeriodo
    GROUP BY t.tipo
";
$stmtTotais = $con->prepare($sqlTotais);
foreach ($params as $k => $v) $stmtTotais->bindValue($k, $v);
$stmtTotais->execute();

$receitaTotal = 0;
$despesaTotal = 0;

while ($row = $stmtTotais->fetch(PDO::FETCH_ASSOC)) {
    if ($row['tipo'] === 'receita') $receitaTotal = $row['total'];
    if ($row['tipo'] === 'despesa') $despesaTotal = $row['total'];
}

/* ============================
   DESPESAS POR CATEGORIA
============================ */
$sqlCat = "
    SELECT c.nome AS categoria, SUM(t.valor) AS total
    FROM transacoes t
    JOIN categorias c ON c.id = t.categoria_id
    WHERE t.usuario_id = :usuario_id
      AND t.tipo = 'despesa'
      $wherePeriodo
    GROUP BY c.id, c.nome
";
$stmtCat = $con->prepare($sqlCat);
foreach ($params as $k => $v) $stmtCat->bindValue($k, $v);
$stmtCat->execute();

$labelsCat  = [];
$valoresCat = [];

while ($row = $stmtCat->fetch(PDO::FETCH_ASSOC)) {
    $labelsCat[]  = $row['categoria'];
    $valoresCat[] = $row['total'];
}

/* ============================
   RECEITAS X DESPESAS POR MÊS
============================ */
$sqlMes = "
    SELECT
        DATE_FORMAT(t.data, '%Y-%m') AS mes,
        SUM(CASE WHEN t.tipo='receita' THEN t.valor ELSE 0 END) AS total_receitas,
        SUM(CASE WHEN t.tipo='despesa' THEN t.valor ELSE 0 END) AS total_despesas
    FROM transacoes t
    WHERE t.usuario_id = :usuario_id
    $wherePeriodo
    GROUP BY DATE_FORMAT(t.data, '%Y-%m')
    ORDER BY mes
";

$stmtMes = $con->prepare($sqlMes);
foreach ($params as $k => $v) $stmtMes->bindValue($k, $v);
$stmtMes->execute();

$meses = [];
$receitasMes = [];
$despesasMes = [];

while ($row = $stmtMes->fetch(PDO::FETCH_ASSOC)) {
    $meses[] = $row['mes'];
    $receitasMes[] = $row['total_receitas'];
    $despesasMes[] = $row['total_despesas'];
}

/* ============================
   SALDO MENSAL
============================ */
$saldoMes = [];
for ($i = 0; $i < count($meses); $i++) {
    $saldoMes[] = $receitasMes[$i] - $despesasMes[$i];
}

/* ============================
   FORMA DE PAGAMENTO POR MÊS
============================ */
$sqlFormaMes = "
    SELECT 
        DATE_FORMAT(data, '%Y-%m') AS mes,
        forma_pagamento,
        SUM(valor) AS total
    FROM transacoes
    WHERE usuario_id = :usuario_id
    $wherePeriodo
    GROUP BY mes, forma_pagamento
    ORDER BY mes ASC
";

$stmtFormaMes = $con->prepare($sqlFormaMes);
foreach ($params as $k => $v) $stmtFormaMes->bindValue($k, $v);
$stmtFormaMes->execute();

$formaMes = [];
while ($row = $stmtFormaMes->fetch(PDO::FETCH_ASSOC)) {
    $formaMes[$row['forma_pagamento']]['meses'][] = $row['mes'];
    $formaMes[$row['forma_pagamento']]['valores'][] = $row['total'];
}

/* ============================
   DESCRIÇÃO X VALOR
============================ */
$sqlDescValor = "
    SELECT descricao, valor, DATE_FORMAT(data, '%Y-%m-%d') AS dia
    FROM transacoes
    WHERE usuario_id = :usuario_id
    $wherePeriodo
    ORDER BY data ASC
";

$stmtDescValor = $con->prepare($sqlDescValor);
foreach ($params as $k => $v) $stmtDescValor->bindValue($k, $v);
$stmtDescValor->execute();

$descLabels = [];
$descValores = [];

while ($row = $stmtDescValor->fetch(PDO::FETCH_ASSOC)) {
    $descLabels[] = $row['dia'] . ' - ' . $row['descricao'];
    $descValores[] = $row['valor'];
}

/* ============================
   DESPESAS POR CATEGORIA AO LONGO DO TEMPO
============================ */
$sqlCatMes = "
    SELECT 
        DATE_FORMAT(t.data, '%Y-%m') AS mes,
        c.nome AS categoria,
        SUM(t.valor) AS total
    FROM transacoes t
    JOIN categorias c ON c.id = t.categoria_id
    WHERE t.usuario_id = :usuario_id
      AND t.tipo = 'despesa'
      $wherePeriodo
    GROUP BY mes, categoria
    ORDER BY mes ASC
";

$stmtCatMes = $con->prepare($sqlCatMes);
foreach ($params as $k => $v) $stmtCatMes->bindValue($k, $v);
$stmtCatMes->execute();

$catMes = [];
while ($row = $stmtCatMes->fetch(PDO::FETCH_ASSOC)) {
    $catMes[$row['categoria']]['meses'][] = $row['mes'];
    $catMes[$row['categoria']]['valores'][] = $row['total'];
}

/* ============================
   INÍCIO DO HTML
============================ */
layout_header("Dashboard - Controle Financeiro");
?>

<h2>Dashboard</h2>

<p style="text-align:center; margin-bottom:20px;"> 
<button onclick="exportarPDF()" 
style="padding:10px 20px; background:#444; color:#fff; 
border:none; border-radius:5px; cursor:pointer;">
 📄 Exportar PDF com Gráficos 
 </button>
 </p>


<p style="text-align:center; margin-bottom:15px;">
    Olá, <strong><?= htmlspecialchars($usuario_nome); ?></strong> |
    <a href="index.php">Lançamentos</a> |
	<a href="configuracoes.php">Exluir Conta</a> |
    <a href="logout.php">Sair</a>
	</p>
	
	<?php if ($_SESSION['is_admin'] == 1): ?> 
<a href="admin.php">Selecionar as contas para serem excluídas</a> | 
<?php endif; ?>
<!--	
<p style="text-align:center; margin-bottom:15px;"> 
<a href="excluir_conta.php" 
style="color:red; font-weight:bold; text-decoration:none;"> 
❌ Excluir minha conta 
</a> 
</p>
-->

<form method="GET" style="margin-bottom:20px;">
    <label>Data início:</label>
    <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio); ?>">

    <label>Data fim:</label>
    <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim); ?>">

    <button type="submit">Filtrar</button>
    <a href="dashboard.php">Limpar</a>
</form>

<hr>

<h3>Resumo geral</h3>
<p><strong>Total de Receitas:</strong> R$ <?= number_format($receitaTotal, 2, ',', '.'); ?></p>
<p><strong>Total de Despesas:</strong> R$ <?= number_format($despesaTotal, 2, ',', '.'); ?></p>
<p><strong>Saldo:</strong> R$ <?= number_format($receitaTotal - $despesaTotal, 2, ',', '.'); ?></p>

<hr>

<!-- ============================= -->
<!--   GRÁFICOS AVANÇADOS         -->
<!-- ============================= -->

<h3>Despesas por Categoria ao Longo do Tempo</h3>
<canvas id="graficoCatMes" height="120"></canvas>

<h3>Forma de Pagamento ao Longo do Tempo</h3>
<canvas id="graficoFormaMes" height="120"></canvas>

<h3>Valores Individuais (Descrição x Valor)</h3>
<canvas id="graficoDescValor" height="120"></canvas>

<h3>Receitas x Despesas por Mês</h3>
<canvas id="graficoMesLinha" height="120"></canvas>

<h3>Saldo Mensal</h3>
<canvas id="graficoSaldoLinha" height="120"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// =============================
// DESPESAS POR CATEGORIA AO LONGO DO TEMPO
// =============================
const catDatasets = [];
<?php foreach ($catMes as $categoria => $dados): ?>
catDatasets.push({
    label: "<?= $categoria ?>",
    data: <?= json_encode($dados['valores']) ?>,
    borderWidth: 3,
    tension: 0.3,
    fill: false
});
<?php endforeach; ?>

new Chart(document.getElementById('graficoCatMes'), {
    type: 'line',
    data: {
        labels: <?= json_encode(reset($catMes)['meses'] ?? []) ?>,
        datasets: catDatasets
    }
});

// =============================
// FORMA DE PAGAMENTO AO LONGO DO TEMPO
// =============================
const formaDatasets = [];
<?php foreach ($formaMes as $forma => $dados): ?>
formaDatasets.push({
    label: "<?= $forma == 'dinheiro' ? '💵 Dinheiro' : '💳 Cartão' ?>",
    data: <?= json_encode($dados['valores']) ?>,
    borderWidth: 3,
    tension: 0.3,
    fill: false
});
<?php endforeach; ?>

new Chart(document.getElementById('graficoFormaMes'), {
    type: 'line',
    data: {
        labels: <?= json_encode(reset($formaMes)['meses'] ?? []) ?>,
        datasets: formaDatasets
    }
});

// =============================
// DESCRIÇÃO X VALOR
// =============================
new Chart(document.getElementById('graficoDescValor'), {
    type: 'line',
    data: {
        labels: <?= json_encode($descLabels) ?>,
        datasets: [{
            label: 'Valor da Transação',
            data: <?= json_encode($descValores) ?>,
            borderColor: 'purple',
            backgroundColor: 'rgba(128,0,128,0.2)',
            borderWidth: 3,
            tension: 0.3,
            fill: true
        }]
    }
});

// =============================
// RECEITAS X DESPESAS POR MÊS
// =============================
new Chart(document.getElementById('graficoMesLinha'), {
    type: 'line',
    data: {
        labels: <?= json_encode($meses) ?>,
        datasets: [
            {
                label: 'Receitas',
                data: <?= json_encode($receitasMes) ?>,
                borderColor: 'green',
                backgroundColor: 'rgba(0,128,0,0.2)',
                borderWidth: 3,
                tension: 0.3,
                fill: true
            },
            {
                label: 'Despesas',
                data: <?= json_encode($despesasMes) ?>,
                borderColor: 'red',
                backgroundColor: 'rgba(255,0,0,0.2)',
                borderWidth: 3,
                tension: 0.3,
                fill: true
            }
        ]
    }
});

// =============================
// SALDO MENSAL
// =============================
new Chart(document.getElementById('graficoSaldoLinha'), {
    type: 'line',
    data: {
        labels: <?= json_encode($meses) ?>,
        datasets: [{
            label: 'Saldo do mês',
            data: <?= json_encode($saldoMes) ?>,
            borderColor: 'rgba(54,162,235,1)',
            backgroundColor: 'rgba(54,162,235,0.3)',
            borderWidth: 3,
            tension: 0.3,
            fill: true
        }]
    }
});
</script>

<script>
// Função para capturar gráficos e enviar ao PHP
function exportarPDF() {

    // Captura todos os gráficos
    const graficos = {
        graficoCatMes: document.getElementById('graficoCatMes').toDataURL(),
        graficoFormaMes: document.getElementById('graficoFormaMes').toDataURL(),
        graficoDescValor: document.getElementById('graficoDescValor').toDataURL(),
        graficoMesLinha: document.getElementById('graficoMesLinha').toDataURL(),
        graficoSaldoLinha: document.getElementById('graficoSaldoLinha').toDataURL()
    };

    // Cria formulário invisível
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'exportar_pdf.php';

    // Envia cada gráfico como campo hidden
    for (const key in graficos) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = graficos[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>


<?php layout_footer(); ?>
