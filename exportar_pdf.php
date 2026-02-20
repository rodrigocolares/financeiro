<?php
require __DIR__ . '/conexao.php';
require __DIR__ . '/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Recebe imagens base64 dos gráficos
$graficoCatMes     = $_POST['graficoCatMes'] ?? null;
$graficoFormaMes   = $_POST['graficoFormaMes'] ?? null;
$graficoDescValor  = $_POST['graficoDescValor'] ?? null;
$graficoMesLinha   = $_POST['graficoMesLinha'] ?? null;
$graficoSaldoLinha = $_POST['graficoSaldoLinha'] ?? null;

// Consulta dados
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
    ORDER BY data DESC
";

$stmt = $con->prepare($sql);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monta HTML
$html = "
<h1 style='text-align:center;'>Relatório Financeiro Completo</h1>
<p style='text-align:center;'>Gerado em: ".date('d/m/Y H:i')."</p>
<hr>

<h2>Gráficos</h2>
";

// Adiciona gráficos ao PDF
function addGrafico($html, $titulo, $img) {
    if ($img) {
        $html .= "<h3>$titulo</h3>
                  <img src='$img' style='width:100%; margin-bottom:20px;'>";
    }
    return $html;
}

$html = addGrafico($html, "Despesas por Categoria ao Longo do Tempo", $graficoCatMes);
$html = addGrafico($html, "Forma de Pagamento ao Longo do Tempo", $graficoFormaMes);
$html = addGrafico($html, "Descrição x Valor", $graficoDescValor);
$html = addGrafico($html, "Receitas x Despesas por Mês", $graficoMesLinha);
$html = addGrafico($html, "Saldo Mensal", $graficoSaldoLinha);

$html .= "
<hr>
<h2>Transações</h2>

<table border='1' cellspacing='0' cellpadding='6' width='100%'>
    <tr style='background:#eee;'>
        <th>Data</th>
        <th>Descrição</th>
        <th>Categoria</th>
        <th>Forma</th>
        <th>Valor</th>
    </tr>
";

foreach ($dados as $linha) {
    $html .= "
    <tr>
        <td>".date('d/m/Y', strtotime($linha['data']))."</td>
        <td>{$linha['descricao']}</td>
        <td>{$linha['categoria_nome']}</td>
        <td>{$linha['forma_pagamento']}</td>
        <td>R$ ".number_format($linha['valor'], 2, ',', '.')."</td>
    </tr>
    ";
}

$html .= "</table>";

// Gera PDF
$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', true);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_financeiro_completo.pdf", ["Attachment" => true]);
exit;
