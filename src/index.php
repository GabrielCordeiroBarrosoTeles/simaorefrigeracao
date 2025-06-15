<?php
// Página inicial do site
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Título da página
$page_title = "Início";

// Incluir o cabeçalho
include_once 'views/public/header.php';

// Conteúdo da página inicial
?>

<div class="container mt-5">
    <div class="jumbotron">
        <h1 class="display-4">Bem-vindo à Simão Refrigeração</h1>
        <p class="lead">Soluções completas em refrigeração para sua casa ou empresa.</p>
        <hr class="my-4">
        <p>Oferecemos serviços de instalação, manutenção e reparo de equipamentos de refrigeração.</p>
        <a class="btn btn-primary btn-lg" href="contato.php" role="button">Entre em contato</a>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Manutenção</h5>
                    <p class="card-text">Serviços de manutenção preventiva e corretiva para todos os tipos de equipamentos de refrigeração.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-fan fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Instalação</h5>
                    <p class="card-text">Instalação profissional de ar condicionado residencial e comercial.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-snowflake fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Câmaras Frigoríficas</h5>
                    <p class="card-text">Projeto, instalação e manutenção de câmaras frigoríficas para empresas.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
include_once 'views/public/footer.php';
?>