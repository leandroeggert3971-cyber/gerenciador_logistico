
<?php
require_once __DIR__ . "/../config/conexao.php";

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["excluir"])) {
        try {
            $sql = $conexao->prepare("DELETE FROM transportadores WHERE id = ?");
            $sql->execute([$_POST["excluir"]]);
            header("Location: index.php?msg=excluido");
            exit;
        } catch (PDOException $e) {
            $erro = "Não foi possível excluir. Este transportador pode estar vinculado a um frete.";
        }
    }

    if (isset($_POST["salvar"])) {
        $id = $_POST["id"] ?? "";
        $nome = trim($_POST["nome"] ?? "");
        $cpf_cnpj = trim($_POST["cpf_cnpj"] ?? "");
        $telefone = trim($_POST["telefone"] ?? "");
        $email = trim($_POST["email"] ?? "");

        if ($nome === "") {
            $erro = "Informe o nome do transportador.";
        } else {
            try {
                if ($id !== "") {
                    $sql = $conexao->prepare(
                        "UPDATE transportadores
                         SET nome=?, cpf_cnpj=?, telefone=?, email=?
                         WHERE id=?"
                    );
                    $sql->execute([$nome, $cpf_cnpj, $telefone, $email, $id]);
                } else {
                    $sql = $conexao->prepare(
                        "INSERT INTO transportadores
                         (nome, cpf_cnpj, telefone, email)
                         VALUES (?, ?, ?, ?)"
                    );
                    $sql->execute([$nome, $cpf_cnpj, $telefone, $email]);
                }

                header("Location: index.php?msg=salvo");
                exit;

            } catch (PDOException $e) {
                $erro = "Erro ao salvar o transportador: " . $e->getMessage();
            }
        }
    }
}

$editar = null;

if (isset($_GET["editar"])) {
    $sql = $conexao->prepare("SELECT * FROM transportadores WHERE id = ?");
    $sql->execute([$_GET["editar"]]);
    $editar = $sql->fetch(PDO::FETCH_ASSOC);
}

$sql = $conexao->query("SELECT * FROM transportadores ORDER BY id DESC");
$transportadores = $sql->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET["msg"])) {
    if ($_GET["msg"] === "salvo") {
        $mensagem = "Transportador salvo com sucesso!";
    } elseif ($_GET["msg"] === "excluido") {
        $mensagem = "Transportador excluído com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Transportadores</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

<div class="container">
    <h1>Cadastro de Transportadores</h1>

    <p><a href="../index.php">Voltar ao menu principal</a></p>

    <?php if ($mensagem): ?>
        <p style="color: green;"><?php echo escapar($mensagem); ?></p>
    <?php endif; ?>

    <?php if ($erro): ?>
        <p style="color: red;"><?php echo escapar($erro); ?></p>
    <?php endif; ?>

    <h2><?php echo $editar ? "Editar Transportador" : "Novo Transportador"; ?></h2>

    <form method="POST">
        <input type="hidden" name="id"
               value="<?php echo escapar($editar["id"] ?? ""); ?>">

        <label>Nome:</label>
        <input type="text" name="nome" required
               value="<?php echo escapar($editar["nome"] ?? ""); ?>">

        <label>CPF/CNPJ:</label>
        <input type="text" name="cpf_cnpj"
               value="<?php echo escapar($editar["cpf_cnpj"] ?? ""); ?>">

        <label>Telefone:</label>
        <input type="text" name="telefone"
               value="<?php echo escapar($editar["telefone"] ?? ""); ?>">

        <label>E-mail:</label>
        <input type="email" name="email"
               value="<?php echo escapar($editar["email"] ?? ""); ?>">

        <button type="submit" name="salvar">Salvar Transportador</button>

        <?php if ($editar): ?>
            <a href="index.php">Cancelar edição</a>
        <?php endif; ?>
    </form>

    <h2>Transportadores Cadastrados</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>CPF/CNPJ</th>
            <th>Telefone</th>
            <th>E-mail</th>
            <th>Ações</th>
        </tr>

        <?php foreach ($transportadores as $transportador): ?>
            <tr>
                <td><?php echo escapar($transportador["id"]); ?></td>
                <td><?php echo escapar($transportador["nome"]); ?></td>
                <td><?php echo escapar($transportador["cpf_cnpj"]); ?></td>
                <td><?php echo escapar($transportador["telefone"]); ?></td>
                <td><?php echo escapar($transportador["email"]); ?></td>
                <td>
                    <a href="index.php?editar=<?php echo escapar($transportador["id"]); ?>">
                        Editar
                    </a>

                    <form method="POST" style="display:inline;"
                          onsubmit="return confirm('Deseja excluir este transportador?');">
                        <button type="submit" name="excluir"
                                value="<?php echo escapar($transportador["id"]); ?>">
                            Excluir
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
