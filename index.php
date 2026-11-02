
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador Logístico</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="text-primary">Gerenciador Logístico</h1>
        <h4>Sistema de Controle de Fretes</h4>
        <p>Bem-vindo ao nosso sistema de gerenciamento logístico!</p>
    </div>

    <h3 class="mb-4">Menu Principal</h3>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Produtos</h5>
                    <p class="card-text">Cadastre e gerencie os produtos.</p>
                    <a href="produtos/index.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Endereços</h5>
                    <p class="card-text">Cadastre origens e destinos.</p>
                    <a href="enderecos/index.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Transportadores</h5>
                    <p class="card-text">Gerencie os transportadores.</p>
                    <a href="transportadores/index.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Fretes</h5>
                    <p class="card-text">Cadastre e consulte os fretes.</p>
                    <a href="fretes/index.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Relatórios</h5>
                    <p class="card-text">Consulte e baixe relatórios de fretes.</p>
                    <a href="relatorios/index.php" class="btn btn-primary">Acessar</a>
                </div>
            </div>
        </div>

    </div>

    <footer class="text-center mt-5">
        <p>Sistema desenvolvido em PHP, MySQL, HTML, CSS e JavaScript.</p>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
