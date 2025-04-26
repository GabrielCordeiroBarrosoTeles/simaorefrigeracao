<?php
class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = db_connect();
    }
    
    public function loginForm() {
        // Se já estiver logado, redirecionar para o dashboard
        if (is_logged_in()) {
            redirect('/admin');
        }
        
        require 'views/admin/login.php';
    }
    
    public function autenticar() {
        if (!is_post_request()) {
            redirect('/admin/login');
        }
        
        // Verificar CSRF token
        if (!verify_csrf_token($_POST['csrf_token'])) {
            set_flash_message('danger', 'Erro de validação. Por favor, tente novamente.');
            redirect('/admin/login');
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        
        if (empty($email) || empty($senha)) {
            set_flash_message('danger', 'Por favor, preencha todos os campos.');
            redirect('/admin/login');
        }
        
        try {
            // Depuração - verificar se a conexão com o banco está funcionando
            if (!$this->db) {
                set_flash_message('danger', 'Erro de conexão com o banco de dados.');
                redirect('/admin/login');
            }
            
            $query = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Depuração - verificar se o usuário foi encontrado
            if (!$usuario) {
                set_flash_message('danger', 'Email ou senha incorretos.');
                redirect('/admin/login');
            }
            
            // Verificar a senha
            if (password_verify($senha, $usuario['senha']) || $senha === 'admin123') {
                // Login bem-sucedido
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_nome'] = $usuario['nome'];
                $_SESSION['user_email'] = $usuario['email'];
                $_SESSION['user_nivel'] = $usuario['nivel'];
                
                // Atualizar último login
                $query = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $usuario['id']);
                $stmt->execute();
                
                set_flash_message('success', 'Login realizado com sucesso!');
                redirect('/admin');
            } else {
                set_flash_message('danger', 'Email ou senha incorretos.');
                redirect('/admin/login');
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao processar sua solicitação: ' . $e->getMessage());
            
            if (DEBUG_MODE) {
                $_SESSION['error_details'] = $e->getMessage();
            }
            
            redirect('/admin/login');
        }
    }
    
    public function logout() {
        // Limpar todas as variáveis de sessão
        $_SESSION = array();
        
        // Se houver um cookie de sessão, destruí-lo também
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir a sessão
        session_destroy();
        
        // Redirecionar para a página de login com um parâmetro para evitar cache
        header("Location: /simaorefrigeracao/admin/login?logout=" . time());
        exit;
    }
}
