
<?php
require_once __DIR__ . "/../config/conexao.php";

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$data = $_GET["data"] ?? date("Y-m-d");

$sql = $conexao->prepare(
    "SELECT
        f.id,
        f.data_frete,
        f.valor_frete,
        f.status,
        t.nome AS transportador,
        o.nome AS origem,
        d.nome AS destino,
        GROUP_CONCAT(
            CONCAT(p.nome, ' (Qtd: ', fp.quantidade, ')')
            SEPARATOR ', '
        ) AS produtos
    FROM fretes f
    INNER JOIN transportadores t ON f.transportador_id = t.id
    INNER JOIN enderecos o ON f.origem_id = o.id
    INNER JOIN enderecos d ON f.destino_id = d.id
    LEFT JOIN frete_produtos fp ON f.id = fp.frete_id
    LEFT JOIN produtos p ON fp.produto_id = p.id
    WHERE f.data_frete = ?
    GROUP BY f.id, f.data_frete, f.valor_frete, f.status,
             t.nome, o.nome, d.nome
    ORDER BY f.id DESC"
);

$sql->execute([$data]);
$fretes = $sql->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET["baixar"])) {
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=relatorio_fretes.csv");

    echo "\xEF\xBB\xBF";

    $saida = fopen("php://output", "w");

    fputcsv($saida, [
        "ID",
        "Data",
        "Transportador",
        "Origem",
        "Destino",
        "Produtos",
        "Valor do Frete",
        "Status"
    ], ";");

    foreach ($fretes as $frete) {
        fputcsv($saida, [
            $frete["id"],
            $frete["data_frete"],
            $frete["transportador"],
            $frete["origem"],
            $frete["destino"],
            $frete["produtos"],
            $frete["valor_frete"],
            $frete["status"]
        ], ";");
    }

    fclose($saida);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Fretes</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

<div class="container">

    <h1>Relatório de Fretes</h1>

    <p><a href="../index.php">Voltar ao menu principal</a></p>

    <form method="GET">
        <label>Selecione a data:</label>

        <input type="date" name="data"
               value="<?php echo escapar($data); ?>" required>

        <button type="submit">Consultar</button>

        <button type="submit" name="baixar" value="1">
            Baixar relatório CSV
        </button>
    </form>

    <h2>Fretes do dia <?php echo escapar($data); ?></h2>

    <?php if (count($fretes) > 0): ?>

        <table border="1" cellpadding="8">
            <tr>
                <th>ID</th>
                <th>Transportador</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Produtos</th>
                <th>Valor</th>
                <th>Status</th>
            </tr>

            <?php foreach ($fretes as $frete): ?>
                <tr>
                    <td><?php echo escapar($frete["id"]); ?></td>
                    <td><?php echo escapar($frete["transportador"]); ?></td>
                    <td><?php echo escapar($frete["origem"]); ?></td>
                    <td><?php echo escapar($frete["destino"]); ?></td>
                    <td><?php echo escapar($frete["produtos"] ?? ""); ?></td>
                    <td>
                        R$ <?php echo number_format(
                            $frete["valor_frete"], 2, ",", "."
                        ); ?>
                    </td>
                    <td><?php echo escapar($frete["status"]); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

    <?php else: ?>

        <p>Nenhum frete encontrado para esta data.</p>

    <?php endif; ?>

</div>

</body>
</html>
