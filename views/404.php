<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada | <?= SITE_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1>404</h1>
            <h2>Página não encontrada</h2>
            <p>A página que você está procurando não existe ou foi movida.</p>
            <a href="/" class="btn btn-primary">Voltar para a página inicial</a>
        </div>
    </div>
    
    <style>
        .error-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8fafc;
        }
        
        .error-content {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #f59e0b;
            margin-bottom: 1rem;
        }
        
        h1 {
            font-size: 6rem;
            font-weight: 700;
            color: #2563eb;
            margin: 0;
            line-height: 1;
        }
        
        h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        p {
            font-size: 1.125rem;
            color: #64748b;
            margin-bottom: 2rem;
        }
    </style>
</body>
</html>
