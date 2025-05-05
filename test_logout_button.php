<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Logout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Teste de Logout</h1>
    
    <div class="info">
        <h2>Informações da Sessão Atual</h2>
        <?php
        session_start();
        if (empty($_SESSION)) {
            echo "<p>Não há sessão ativa. Você não está logado.</p>";
        } else {
            echo "<p>Você está logado como: <strong>" . ($_SESSION['user_nome'] ?? 'Usuário desconhecido') . "</strong></p>";
            echo "<p>ID do usuário: " . ($_SESSION['user_id'] ?? 'N/A') . "</p>";
            echo "<p>Nível: " . ($_SESSION['user_nivel'] ?? 'N/A') . "</p>";
        }
        ?>
    </div>
    
    <p>Clique no botão abaixo para testar o logout:</p>
    
    <a href="logout.php" class="btn" onclick="return confirm('Tem certeza que deseja sair?')">Fazer Logout</a>
    
    <p style="margin-top: 20px;">
        <a href="admin-dashboard.php">Voltar para o Dashboard</a>
    </p>
</body>
</html>
