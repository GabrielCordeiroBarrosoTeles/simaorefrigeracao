<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo | <?= SITE_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-snowflake"></i>
                <span>Simão Refrigeração</span>
            </div>
            <h1>Painel Administrativo</h1>
        </div>
        
        <?php
        // Exibir mensagem flash
        $flash_message = get_flash_message();
        if ($flash_message) {
            echo '<div class="alert alert-' . $flash_message['type'] . '">' . $flash_message['message'] . '</div>';
        }
        ?>
        
        <div class="login-form">
            <form method="POST" action="/admin/autenticar">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>
        </div>
        
        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Todos os direitos reservados.</p>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>
