<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?>Simão Refrigeração</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/simaorefrigeracao/public/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/simaorefrigeracao/">
                <i class="fas fa-snowflake mr-2"></i>
                Simão Refrigeração
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item <?= $page_title === 'Início' ? 'active' : '' ?>">
                        <a class="nav-link" href="/simaorefrigeracao/">Início</a>
                    </li>
                    <li class="nav-item <?= $page_title === 'Serviços' ? 'active' : '' ?>">
                        <a class="nav-link" href="/simaorefrigeracao/servicos.php">Serviços</a>
                    </li>
                    <li class="nav-item <?= $page_title === 'Sobre' ? 'active' : '' ?>">
                        <a class="nav-link" href="/simaorefrigeracao/sobre.php">Sobre</a>
                    </li>
                    <li class="nav-item <?= $page_title === 'Contato' ? 'active' : '' ?>">
                        <a class="nav-link" href="/simaorefrigeracao/contato.php">Contato</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/simaorefrigeracao/admin-login.php">Área Restrita</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>