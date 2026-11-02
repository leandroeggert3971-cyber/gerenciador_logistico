
<?php

require_once "../config/conexao.php";

function limpar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$mensagem = "";

// Excluir endereço
if (isset($_POST["excluir"])) {

    $id = (int) $_POST["excluir"];

    try {
        $stmt = $conexao->prepare(
            "DELETE FROM enderecos WHERE id = ?"
        );

        $stmt->execute([$id]);

        header("Location: index.php?msg=excluido");
        exit;

    } catch (PDOException $erro) {
        header("Location: index.php?msg=erro_exclusao");
        exit;
    }
}

// Cadastrar ou editar endereço
if ($_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST["salvar"])) {

    $id = (int) ($_POST["id"] ?? 0);
    $tipo = $_POST["tipo"] ?? "";
    $nome = trim($_POST["nome"] ?? "");
    $cep = trim($_POST["cep"] ?? "");
    $rua = trim($_POST["rua"] ?? "");
    $numero = trim($_POST["numero"] ?? "");
    $bairro = trim($_POST["bairro"] ?? "");
    $cidade = trim($_POST["cidade"] ?? "");
    $estado = strtoupper(trim($_POST["estado"] ?? ""));

    if (
        in_array($tipo, ["Origem", "Destino"], true) &&
        $nome !== "" &&
        $rua !== "" &&
        $cidade !== "" &&
        preg_match('/^[A-Z]{2}$/', $estado)
    ) {

        if ($id > 0) {

            $sql = "UPDATE enderecos SET
                tipo = :tipo,
                nome = :nome,
                cep = :cep,
                rua = :rua,
                numero = :numero,
                bairro = :bairro,
                cidade = :cidade,
                estado = :estado
                WHERE id = :id";

        } else {

            $sql = "INSERT INTO enderecos
                (tipo, nome, cep, rua, numero,
                 bairro, cidade, estado)
                VALUES
                (:tipo, :nome, :cep, :rua, :numero,
                 :bairro, :cidade, :estado)";
        }

        $stmt = $conexao->prepare($sql);

        $dados = [
            ":tipo" => $tipo,
            ":nome" => $nome,
            ":cep" => $cep,
            ":rua" => $rua,
            ":numero" => $numero,
            ":bairro" => $bairro,
            ":cidade" => $cidade,
            ":estado" => $estado
        ];

        if ($id > 0) {
            $dados[":id"] = $id;
        }

        $stmt->execute($dados);

        header("Location: index.php?msg=" .
            ($id > 0 ? "atualizado" : "cadastrado"));
        exit;

    } else {
        $mensagem = "Confira os campos obrigatórios e o estado.";
    }
}

// Buscar endereço para edição
$enderecoEdicao = null;

if (isset($_GET["editar"])) {

    $id = (int) $_GET["editar"];

    $stmt = $conexao->prepare(
        "SELECT * FROM enderecos WHERE id = ?"
    );

    $stmt->execute([$id]);

    $enderecoEdicao = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Mensagens
$mensagens = [
    "cadastrado" => "Endereço cadastrado com sucesso!",
    "atualizado" => "Endereço atualizado com sucesso!",
    "excluido" => "Endereço excluído com sucesso!",
    "erro_exclusao" => "Não foi possível excluir. Este endereço pode estar vinculado a um frete."
];

$mensagem = $mensagens[$_GET["msg"] ?? ""] ?? $mensagem;

// Listar endereços
$enderecos = $conexao->query(
    "SELECT * FROM enderecos ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Endereços</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

    <h2 class="mb-4">Gerenciamento de Endereços</h2>

    <a href="../index.php" class="btn btn-secondary mb-3">
        Voltar ao painel
    </a>

    <?php if ($mensagem): ?>
        <div class="alert alert-info">
            <?= limpar($mensagem) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">

        <div class="card-header bg-primary text-white">

            <?= $enderecoEdicao ? "Editar Endereço" : "Novo Endereço" ?>

        </div>

        <div class="card-body">

            <form method="POST">

                <input type="hidden" name="id"
                       value="<?= limpar($enderecoEdicao["id"] ?? "") ?>">

                <div class="mb-3">

                    <label class="form-label">Tipo de endereço</label>

                    <select name="tipo" class="form-select" required>

                        <option value="Origem"
                            <?= ($enderecoEdicao["tipo"] ?? "") == "Origem" ? "selected" : "" ?>>
                            Origem
                        </option>

                        <option value="Destino"
                            <?= ($enderecoEdicao["tipo"] ?? "") == "Destino" ? "selected" : "" ?>>
                            Destino
                        </option>

                    </select>

                </div>

                <div class="mb-3">
                    <label class="form-label">Identificação do endereço</label>

                    <input type="text" name="nome"
                           class="form-control"
                           placeholder="Ex.: Depósito Central"
                           value="<?= limpar($enderecoEdicao["nome"] ?? "") ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">CEP</label>

                    <input type="text" name="cep"
                           class="form-control"
                           placeholder="00000-000"
                           value="<?= limpar($enderecoEdicao["cep"] ?? "") ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Rua</label>

                    <input type="text" name="rua"
                           class="form-control"
                           value="<?= limpar($enderecoEdicao["rua"] ?? "") ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Número</label>

                    <input type="text" name="numero"
                           class="form-control"
                           value="<?= limpar($enderecoEdicao["numero"] ?? "") ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Bairro</label>

                    <input type="text" name="bairro"
                           class="form-control"
                           value="<?= limpar($enderecoEdicao["bairro"] ?? "") ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Cidade</label>

                    <input type="text" name="cidade"
                           class="form-control"
                           value="<?= limpar($enderecoEdicao["cidade"] ?? "") ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Estado (UF)</label>

                    <input type="text" name="estado"
                           class="form-control"
                           maxlength="2"
                           placeholder="PR"
                           value="<?= limpar($enderecoEdicao["estado"] ?? "") ?>"
                           required>
                </div>

                <button type="submit" name="salvar"
                        class="btn btn-success">

                    <?= $enderecoEdicao ? "Salvar Alterações" : "Cadastrar Endereço" ?>

                </button>

                <?php if ($enderecoEdicao): ?>
                    <a href="index.php" class="btn btn-secondary">
                        Cancelar
                    </a>
                <?php endif; ?>

            </form>

        </div>
    </div>

    <h4>Endereços cadastrados</h4>

    <div class="table-responsive">

        <table class="table table-bordered table-striped bg-white">

            <thead class="table-dark">

                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Identificação</th>
                    <th>Rua</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($enderecos as $endereco): ?>

                    <tr>

                        <td><?= $endereco["id"] ?></td>

                        <td><?= limpar($endereco["tipo"]) ?></td>

                        <td><?= limpar($endereco["nome"]) ?></td>

                        <td>
                            <?= limpar($endereco["rua"]) ?>,
                            <?= limpar($endereco["numero"]) ?>
                        </td>

                        <td><?= limpar($endereco["cidade"]) ?></td>

                        <td><?= limpar($endereco["estado"]) ?></td>

                        <td>

                            <a href="index.php?editar=<?= $endereco["id"] ?>"
                               class="btn btn-warning btn-sm">
                                Editar
                            </a>

                            <form method="POST" class="d-inline"
                                  onsubmit="return confirm('Deseja excluir este endereço?')">

                                <button type="submit"
                                        name="excluir"
                                        value="<?= $endereco["id"] ?>"
                                        class="btn btn-danger btn-sm">
                                    Excluir
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (empty($enderecos)): ?>

                    <tr>
                        <td colspan="7" class="text-center">
                            Nenhum endereço cadastrado.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
