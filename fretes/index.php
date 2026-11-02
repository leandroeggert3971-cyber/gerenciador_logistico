
<?php
require_once __DIR__ . "/../config/conexao.php";

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$erro = "";
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $transportador_id = $_POST["transportador_id"] ?? "";
    $origem_id = $_POST["origem_id"] ?? "";
    $destino_id = $_POST["destino_id"] ?? "";
    $data_frete = $_POST["data_frete"] ?? "";
    $valor_frete = $_POST["valor_frete"] ?? "";
    $produtos = $_POST["produtos"] ?? [];
    $quantidades = $_POST["quantidades"] ?? [];

    if (
        !$transportador_id || !$origem_id || !$destino_id ||
        !$data_frete || $valor_frete === "" ||
        !is_numeric($valor_frete) || $valor_frete < 0 ||
        !is_array($produtos) || count($produtos) === 0
    ) {
        $erro = "Preencha todos os campos e selecione pelo menos um produto.";
    } elseif ($origem_id == $destino_id) {
        $erro = "A origem e o destino devem ser diferentes.";
    } else {
        try {
            $sql = $conexao->prepare(
                "SELECT id FROM enderecos WHERE id = ? AND tipo = 'Origem'"
            );
            $sql->execute([$origem_id]);

            if (!$sql->fetch()) {
                throw new Exception("O endereço selecionado não é uma origem.");
            }

            $sql = $conexao->prepare(
                "SELECT id FROM enderecos WHERE id = ? AND tipo = 'Destino'"
            );
            $sql->execute([$destino_id]);

            if (!$sql->fetch()) {
                throw new Exception("O endereço selecionado não é um destino.");
            }

            $sql = $conexao->prepare(
                "SELECT id FROM transportadores WHERE id = ?"
            );
            $sql->execute([$transportador_id]);

            if (!$sql->fetch()) {
                throw new Exception("Transportador inválido.");
            }

            $conexao->beginTransaction();

            $sql = $conexao->prepare(
                "INSERT INTO fretes
                (transportador_id, origem_id, destino_id, data_frete, valor_frete, status)
                VALUES (?, ?, ?, ?, ?, 'Pendente')"
            );

            $sql->execute([
                $transportador_id,
                $origem_id,
                $destino_id,
                $data_frete,
                $valor_frete
            ]);

            $frete_id = $conexao->lastInsertId();

            $sqlProduto = $conexao->prepare(
                "INSERT INTO frete_produtos
                (frete_id, produto_id, quantidade)
                VALUES (?, ?, ?)"
            );

            foreach ($produtos as $produto_id) {
                $quantidade = $quantidades[$produto_id] ?? 0;

                if (!filter_var($quantidade, FILTER_VALIDATE_INT) || $quantidade < 1) {
                    throw new Exception("Informe uma quantidade válida para cada produto.");
                }

                $sqlProduto->execute([
                    $frete_id,
                    $produto_id,
                    $quantidade
                ]);
            }

            $conexao->commit();

            header("Location: index.php?msg=salvo");
            exit;

        } catch (Throwable $e) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }

            $erro = "Não foi possível cadastrar o frete: " . $e->getMessage();
        }
    }
}

$transportadores = $conexao->query(
    "SELECT id, nome FROM transportadores ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$origens = $conexao->query(
    "SELECT id, nome, cidade FROM enderecos WHERE tipo = 'Origem' ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$destinos = $conexao->query(
    "SELECT id, nome, cidade FROM enderecos WHERE tipo = 'Destino' ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$listaProdutos = $conexao->query(
    "SELECT id, nome, peso FROM produtos ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$fretes = $conexao->query(
    "SELECT f.*, t.nome AS transportador,
    o.nome AS origem, d.nome AS destino
    FROM fretes f
    INNER JOIN transportadores t ON f.transportador_id = t.id
    INNER JOIN enderecos o ON f.origem_id = o.id
    INNER JOIN enderecos d ON f.destino_id = d.id
    ORDER BY f.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET["msg"]) && $_GET["msg"] === "salvo") {
    $mensagem = "Frete cadastrado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Fretes</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

<div class="container">

    <h1>Gerenciador de Fretes</h1>

    <p><a href="../index.php">Voltar ao menu principal</a></p>

    <?php if ($mensagem): ?>
        <p style="color:green;"><?php echo escapar($mensagem); ?></p>
    <?php endif; ?>

    <?php if ($erro): ?>
        <p style="color:red;"><?php echo escapar($erro); ?></p>
    <?php endif; ?>

    <h2>Novo Frete</h2>

    <form method="POST">

        <label>Transportador:</label>
        <select name="transportador_id" required>
            <option value="">Selecione</option>
            <?php foreach ($transportadores as $t): ?>
                <option value="<?php echo escapar($t["id"]); ?>">
                    <?php echo escapar($t["nome"]); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Endereço de origem:</label>
        <select name="origem_id" required>
            <option value="">Selecione</option>
            <?php foreach ($origens as $o): ?>
                <option value="<?php echo escapar($o["id"]); ?>">
                    <?php echo escapar($o["nome"] . " - " . $o["cidade"]); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Endereço de destino:</label>
        <select name="destino_id" required>
            <option value="">Selecione</option>
            <?php foreach ($destinos as $d): ?>
                <option value="<?php echo escapar($d["id"]); ?>">
                    <?php echo escapar($d["nome"] . " - " . $d["cidade"]); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Data do frete:</label>
        <input type="date" name="data_frete" required>

        <label>Valor do frete (R$):</label>
        <input type="number" name="valor_frete"
               min="0" step="0.01" required>

<h3>Produtos transportados</h3>

<div class="mb-3">
    <label for="produto_select" class="form-label">Escolha um produto:</label>

    <select id="produto_select" class="form-select">
        <option value="">Selecione um produto</option>

        <?php foreach ($listaProdutos as $p): ?>
            <option value="<?php echo escapar($p["id"]); ?>">
                <?php echo escapar($p["nome"]); ?>
                (Peso: <?php echo escapar($p["peso"]); ?> kg)
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-3">
    <label for="quantidade_produto" class="form-label">Quantidade:</label>
    <input type="number" id="quantidade_produto"
           class="form-control" min="1" value="1">
</div>

<button type="button" id="adicionar_produto" class="btn btn-success mb-3">
    Adicionar produto
</button>

<div id="produtos_selecionados" class="mb-3">
    <p>Nenhum produto adicionado.</p>
</div>

      

        <button type="submit">Cadastrar Frete</button>

    </form>

    <h2>Fretes cadastrados</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Transportador</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Data</th>
            <th>Valor</th>
            <th>Status</th>
        </tr>

        <?php foreach ($fretes as $f): ?>
            <tr>
                <td><?php echo escapar($f["id"]); ?></td>
                <td><?php echo escapar($f["transportador"]); ?></td>
                <td><?php echo escapar($f["origem"]); ?></td>
                <td><?php echo escapar($f["destino"]); ?></td>
                <td><?php echo escapar($f["data_frete"]); ?></td>
                <td>R$ <?php echo number_format($f["valor_frete"], 2, ",", "."); ?></td>
                <td><?php echo escapar($f["status"]); ?></td>
            </tr>
        <?php endforeach; ?>

    </table>

</div>
<script src="../js/script.js"></script>
</body>
</html>
