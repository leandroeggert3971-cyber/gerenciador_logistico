
<?php
require_once "../config/conexao.php";

$mensagem = "";

function limpar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Excluir produto
if (isset($_POST["excluir"])) {

    $id = (int) $_POST["excluir"];

    $stmt = $conexao->prepare(
        "DELETE FROM produtos WHERE id = ?"
    );

    $stmt->execute([$id]);

    header("Location: index.php?msg=excluido");
    exit;
}

// Cadastrar ou editar produto
if ($_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST["salvar"])) {

    $id = (int) ($_POST["id"] ?? 0);
    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $peso = $_POST["peso"] ?? "";
    $valor = $_POST["valor"] ?? "";

    if ($nome !== "" && is_numeric($peso) && $peso > 0) {

        if ($id > 0) {

            $sql = "UPDATE produtos SET
                    nome = :nome,
                    descricao = :descricao,
                    peso = :peso,
                    valor = :valor
                    WHERE id = :id";

            $mensagem = "Produto atualizado com sucesso!";

        } else {

            $sql = "INSERT INTO produtos
                    (nome, descricao, peso, valor)
                    VALUES (:nome, :descricao, :peso, :valor)";

            $mensagem = "Produto cadastrado com sucesso!";
        }

        $stmt = $conexao->prepare($sql);

        $dados = [
            ":nome" => $nome,
            ":descricao" => $descricao,
            ":peso" => $peso,
            ":valor" => $valor !== "" ? $valor : null
        ];

        if ($id > 0) {
            $dados[":id"] = $id;
        }

        $stmt->execute($dados);

        header("Location: index.php?msg=" .
            ($id > 0 ? "atualizado" : "cadastrado"));
        exit;

    } else {
        $mensagem = "Informe o nome e um peso válido.";
    }
}

// Buscar produto para edição
$produtoEdicao = null;

if (isset($_GET["editar"])) {

    $id = (int) $_GET["editar"];

    $stmt = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $stmt->execute([$id]);

    $produtoEdicao = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Mensagens
$mensagens = [
    "cadastrado" => "Produto cadastrado com sucesso!",
    "atualizado" => "Produto atualizado com sucesso!",
    "excluido" => "Produto excluído com sucesso!"
];

$mensagem = $mensagens[$_GET["msg"] ?? ""] ?? $mensagem;

// Listar produtos
$produtos = $conexao->query(
    "SELECT * FROM produtos ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Produtos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h2 class="mb-4">Gerenciamento de Produtos</h2>

    <a href="../index.php" class="btn btn-secondary mb-3">
        Voltar ao painel
    </a>

    <?php if ($mensagem): ?>
        <div class="alert alert-success">
            <?= limpar($mensagem) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">

        <div class="card-header bg-primary text-white">

            <?= $produtoEdicao ? "Editar Produto" : "Novo Produto" ?>

        </div>

        <div class="card-body">

            <form method="POST">

                <input type="hidden" name="id"
                       value="<?= limpar($produtoEdicao["id"] ?? "") ?>">

                <div class="mb-3">
                    <label class="form-label">Nome do produto</label>

                    <input type="text" name="nome"
                           class="form-control"
                           value="<?= limpar($produtoEdicao["nome"] ?? "") ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição</label>

                    <textarea name="descricao"
                              class="form-control"><?= limpar($produtoEdicao["descricao"] ?? "") ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Peso (kg)</label>

                    <input type="number" name="peso"
                           class="form-control"
                           step="0.01" min="0.01"
                           value="<?= limpar($produtoEdicao["peso"] ?? "") ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Valor (R$)</label>

                    <input type="number" name="valor"
                           class="form-control"
                           step="0.01" min="0"
                           value="<?= limpar($produtoEdicao["valor"] ?? "") ?>">
                </div>

                <button type="submit" name="salvar"
                        class="btn btn-success">
                    <?= $produtoEdicao ? "Salvar Alterações" : "Cadastrar Produto" ?>
                </button>

                <?php if ($produtoEdicao): ?>
                    <a href="index.php" class="btn btn-secondary">
                        Cancelar
                    </a>
                <?php endif; ?>

            </form>

        </div>
    </div>

    <h4>Produtos cadastrados</h4>

    <div class="table-responsive">

        <table class="table table-bordered table-striped bg-white">

            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Descrição</th>
                    <th>Peso</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($produtos as $produto): ?>

                    <tr>
                        <td><?= $produto["id"] ?></td>

                        <td><?= limpar($produto["nome"]) ?></td>

                        <td><?= limpar($produto["descricao"]) ?></td>

                        <td><?= limpar($produto["peso"]) ?> kg</td>

                        <td>R$ <?= limpar($produto["valor"]) ?></td>

                        <td>
                            <a href="index.php?editar=<?= $produto["id"] ?>"
                               class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Deseja excluir este produto?')">

                                <button type="submit"
                                        name="excluir"
                                        value="<?= $produto["id"] ?>"
                                        class="btn btn-danger btn-sm">
                                    Excluir
                                </button>

                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>

                <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            Nenhum produto cadastrado.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
