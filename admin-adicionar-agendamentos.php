<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

// Verificar se o usuário é um administrador
if ($_SESSION['user_nivel'] !== 'admin') {
    header('Location: admin-dashboard.php');
    exit;
}

// Título da página
$page_title = "Adicionar Agendamentos de Exemplo";

// Processar formulário
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_agendamentos'])) {
    try {
        // Fazer uma requisição para o script que adiciona agendamentos
        $ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/simaorefrigeracao/adicionar-agendamentos.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            $message = $result['message'];
            $message_type = 'success';
        } else {
            $message = $result['message'] ?? 'Erro ao adicionar agendamentos';
            $message_type = 'danger';
        }
    } catch (Exception $e) {
        $message = 'Erro: ' . $e->getMessage();
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | <?= defined('SITE_NAME') ? SITE_NAME : 'Simão Refrigeração' ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f6f9;
            padding: 2rem;
        }
        
        .container {
            max-width: 800px;
        }
        
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #2563eb;
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
            padding: 1rem 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="h4 mb-0"><?= $page_title ?></h1>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <p class="mb-4">Esta ferramenta irá adicionar agendamentos de exemplo ao sistema para fins de demonstração.</p>
                
                <form method="post" action="">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Atenção:</strong> Esta ação irá adicionar vários agendamentos de exemplo ao sistema. Só use esta ferramenta em ambientes de teste ou demonstração.
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="adicionar_agendamentos" class="btn btn-primary">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Adicionar Agendamentos de Exemplo
                        </button>
                        <a href="admin-dashboard.php" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Voltar para o Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>