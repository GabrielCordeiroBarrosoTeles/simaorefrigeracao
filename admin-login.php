<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    header('Location: admin-dashboard.php');
    exit;
}

// Verificar se é uma requisição de logout
if (isset($_GET['logout'])) {
    // Limpar todas as variáveis de sessão
    $_SESSION = [];
    
    // Destruir a sessão
    session_destroy();
    
    // Redirecionar para a página de login
    header('Location: admin-login.php');
    exit;
}

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    // Validação básica
    if (empty($email) || empty($senha)) {
        $error_message = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $db = db_connect();
            
            // Verificar se a conexão com o banco está funcionando
            if (!$db) {
                $error_message = 'Erro de conexão com o banco de dados.';
            } else {
                // Depuração - verificar a consulta SQL
                $query = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Para depuração
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    error_log("Tentativa de login para: " . $email);
                    if ($usuario) {
                        error_log("Usuário encontrado: " . $usuario['nome']);
                        error_log("Senha armazenada: " . $usuario['senha']);
                    } else {
                        error_log("Usuário não encontrado");
                    }
                }
                
                // Se o usuário não for encontrado, tentar criar um usuário de teste
                if (!$usuario && ($email == 'admin@friocerto.com.br' || $email == 'carlos@simaorefrigeracao.com.br' || $email == 'simaorefrigeracao2@gmail.com')) {
                    // Criar usuário de teste
                    $nivel = ($email == 'carlos@simaorefrigeracao.com.br') ? 'tecnico' : 'admin';
                    $nome = ($email == 'carlos@simaorefrigeracao.com.br') ? 'Carlos' : 'Administrador';
                    
                    $query = "INSERT INTO usuarios (nome, email, senha, nivel, ultimo_login) 
                              VALUES (:nome, :email, :senha, :nivel, NOW())";
                    $stmt = $db->prepare($query);
                    $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $stmt->bindParam(':nome', $nome);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':senha', $senha_hash);
                    $stmt->bindParam(':nivel', $nivel);
                    $stmt->execute();
                    
                    // Buscar o usuário recém-criado
                    $query = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (defined('DEBUG_MODE') && DEBUG_MODE) {
                        error_log("Usuário criado: " . $nome);
                    }
                }
                
                // Verificar se o usuário foi encontrado
                if (!$usuario) {
                    $error_message = 'Email ou senha incorretos.';
                } else {
                    // Verificar a senha - para testes, aceitamos 'admin123' para qualquer usuário
                    if ($senha === 'admin123' || password_verify($senha, $usuario['senha'])) {
                        // Login bem-sucedido
                        $_SESSION['user_id'] = $usuario['id'];
                        $_SESSION['user_nome'] = $usuario['nome'];
                        $_SESSION['user_email'] = $usuario['email'];
                        $_SESSION['user_nivel'] = $usuario['nivel'];
                        
                        // Registrar login
                        $query = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':id', $usuario['id']);
                        $stmt->execute();
                        
                        // Redirecionar com base no nível do usuário
                        if ($usuario['nivel'] === 'tecnico') {
                            header('Location: tecnico-dashboard.php');
                        } else {
                            header('Location: admin-dashboard.php');
                        }
                        exit;
                    } else {
                        $error_message = 'Email ou senha incorretos.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Erro ao processar sua solicitação: ' . (defined('DEBUG_MODE') && DEBUG_MODE ? $e->getMessage() : 'Tente novamente mais tarde.');
            
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("Erro PDO: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo | <?= defined('SITE_NAME') ? SITE_NAME : 'Simão Refrigeração' ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .login-header .logo i {
            margin-right: 10px;
            font-size: 28px;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: var(--gray);
            margin-bottom: 0;
        }
        
        .login-form {
            margin-bottom: 30px;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .login-form .input-icon {
            position: relative;
        }
        
        .login-form .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .login-form input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .login-form .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
            z-index: 10;
            background: transparent;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
        
        .login-form .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .login-form .remember-me input {
            width: auto;
            margin-right: 10px;
            padding: 0;
        }
        
        .login-form button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-form button:hover {
            background-color: var(--primary-dark);
        }
        
        .login-form button i {
            margin-right: 8px;
        }
        
        .login-footer {
            text-align: center;
            font-size: 14px;
            color: var(--gray);
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            position: relative;
            animation: slideIn 0.3s ease-in-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }
        
        .back-to-site {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-to-site:hover {
            color: var(--primary-dark);
            text-decoration: none;
        }
        
        .back-to-site i {
            margin-right: 5px;
        }
        
        /* Responsividade */
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
            }
            
            .login-header h1 {
                font-size: 20px;
            }
            
            .login-form input {
                font-size: 14px;
                padding: 10px 15px 10px 40px;
            }
            
            .login-form button {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-snowflake"></i>
                <span>Simão Refrigeração</span>
            </div>
            <h1>Painel Administrativo</h1>
            <p>Faça login para acessar o sistema</p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <?php
        // Exibir mensagem flash
        if (function_exists('get_flash_message')) {
            $flash_message = get_flash_message();
            if ($flash_message): ?>
                <div class="alert alert-<?= $flash_message['type'] ?>">
                    <?= $flash_message['message'] ?>
                </div>
            <?php endif;
        }
        ?>
        
        <div class="login-form">
            <form method="POST" action="" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="seu@email.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Lembrar-me</label>
                </div>
                
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
            
            <div class="text-center mt-3">
                <a href="index.php" class="back-to-site">
                    <i class="fas fa-arrow-left"></i> Voltar para o site
                </a>
            </div>
        </div>
        
        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> <?= defined('SITE_NAME') ? SITE_NAME : 'Simão Refrigeração' ?>. Todos os direitos reservados.</p>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para mostrar/ocultar senha
        function togglePassword() {
            const passwordInput = document.getElementById('senha');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Adicionar classe de foco aos inputs
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>
