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

// Proteção contra ataques de força bruta
function checkLoginAttempts($ip) {
    // Verificar se existe arquivo de tentativas
    $attempts_file = 'login_attempts.json';
    $max_attempts = 5; // Máximo de tentativas
    $lockout_time = 15 * 60; // 15 minutos em segundos
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true);
    } else {
        $attempts = [];
    }
    
    // Limpar tentativas antigas
    foreach ($attempts as $attempt_ip => $data) {
        if (time() - $data['time'] > $lockout_time) {
            unset($attempts[$attempt_ip]);
        }
    }
    
    // Verificar se o IP está bloqueado
    if (isset($attempts[$ip]) && $attempts[$ip]['count'] >= $max_attempts) {
        $time_remaining = $lockout_time - (time() - $attempts[$ip]['time']);
        if ($time_remaining > 0) {
            $minutes = ceil($time_remaining / 60);
            return "Muitas tentativas de login. Tente novamente em $minutes minutos.";
        } else {
            // Resetar contagem se o tempo expirou
            unset($attempts[$ip]);
        }
    }
    
    // Salvar tentativas
    file_put_contents($attempts_file, json_encode($attempts));
    return false;
}

// Registrar tentativa de login falha
function recordFailedAttempt($ip) {
    $attempts_file = 'login_attempts.json';
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true);
    } else {
        $attempts = [];
    }
    
    if (isset($attempts[$ip])) {
        $attempts[$ip]['count']++;
        $attempts[$ip]['time'] = time();
    } else {
        $attempts[$ip] = [
            'count' => 1,
            'time' => time()
        ];
    }
    
    file_put_contents($attempts_file, json_encode($attempts));
}

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Verificar tentativas de login
    $blocked = checkLoginAttempts($ip);
    if ($blocked) {
        $error_message = $blocked;
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $remember = isset($_POST['remember']) ? true : false;
        
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
                    // Verificar CSRF token
                    if (!verify_csrf_token($_POST['csrf_token'])) {
                        $error_message = 'Erro de validação de segurança. Por favor, tente novamente.';
                    } else {
                        // Depuração - verificar a consulta SQL
                        $query = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':email', $email);
                        $stmt->execute();
                        
                        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Verificar se o usuário foi encontrado
                        if (!$usuario) {
                            $error_message = 'Email ou senha incorretos.';
                            recordFailedAttempt($ip);
                        } else {
                            // Verificar a senha - aceita tanto hash quanto 'admin123' para facilitar testes
                            if (password_verify($senha, $usuario['senha']) || $senha === 'admin123') {
                                // Login bem-sucedido
                                $_SESSION['user_id'] = $usuario['id'];
                                $_SESSION['user_nome'] = $usuario['nome'];
                                $_SESSION['user_email'] = $usuario['email'];
                                $_SESSION['user_nivel'] = $usuario['nivel'];
                                $_SESSION['last_activity'] = time(); // Para timeout de sessão
                                
                                // Registrar login
                                $query = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
                                $stmt = $db->prepare($query);
                                $stmt->bindParam(':id', $usuario['id']);
                                $stmt->execute();
                                
                                // Configurar cookie "lembrar-me" se solicitado
                                if ($remember) {
                                    $token = bin2hex(random_bytes(32));
                                    $expiry = time() + (30 * 24 * 60 * 60); // 30 dias
                                    
                                    // Salvar token no banco de dados
                                    $query = "UPDATE usuarios SET remember_token = :token, token_expiry = :expiry WHERE id = :id";
                                    $stmt = $db->prepare($query);
                                    $stmt->bindParam(':token', $token);
                                    $stmt->bindParam(':expiry', $expiry);
                                    $stmt->bindParam(':id', $usuario['id']);
                                    $stmt->execute();
                                    
                                    // Definir cookie seguro
                                    setcookie('remember_token', $token, $expiry, '/', '', false, true);
                                }
                                
                                // Redirecionar para o dashboard
                                header('Location: admin-dashboard.php');
                                exit;
                            } else {
                                $error_message = 'Email ou senha incorretos.';
                                recordFailedAttempt($ip);
                            }
                        }
                    }
                }
            } catch (PDOException $e) {
                $error_message = 'Erro ao processar sua solicitação: ' . ($DEBUG_MODE ? $e->getMessage() : 'Tente novamente mais tarde.');
                
                if (DEBUG_MODE) {
                    $error_details = $e->getMessage();
                }
            }
        }
    }
}

// Verificar cookie "lembrar-me"
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        $db = db_connect();
        $query = "SELECT * FROM usuarios WHERE remember_token = :token AND token_expiry > :now LIMIT 1";
        $stmt = $db->prepare($query);
        $now = time();
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            // Login automático
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nome'] = $usuario['nome'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_nivel'] = $usuario['nivel'];
            $_SESSION['last_activity'] = time();
            
            // Redirecionar para o dashboard
            header('Location: admin-dashboard.php');
            exit;
        }
    } catch (PDOException $e) {
        // Ignorar erro e continuar para a página de login
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo | <?= SITE_NAME ?></title>
    
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
        $flash_message = get_flash_message();
        if ($flash_message): ?>
            <div class="alert alert-<?= $flash_message['type'] ?>">
                <?= $flash_message['message'] ?>
            </div>
        <?php endif; ?>
        
        <div class="login-form">
            <form method="POST" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
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
                <p class="text-muted">
                    <small>Email: admin@friocerto.com.br</small><br>
                    <small>Senha: admin123</small>
                </p>
                <a href="index.php" class="back-to-site">
                    <i class="fas fa-arrow-left"></i> Voltar para o site
                </a>
            </div>
        </div>
        
        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Todos os direitos reservados.</p>
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
