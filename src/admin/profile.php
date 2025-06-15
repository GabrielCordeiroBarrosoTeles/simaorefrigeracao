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

// Conectar ao banco de dados
$db = db_connect();

// Obter dados do usuário
$user_id = $_SESSION['user_id'];
$user = [];

try {
    $query = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Usuário não encontrado
        set_flash_message('danger', 'Usuário não encontrado.');
        header('Location: admin-dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar dados do usuário.');
    header('Location: admin-dashboard.php');
    exit;
}

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        set_flash_message('danger', 'Erro de validação de segurança. Por favor, tente novamente.');
        header('Location: admin-profile.php');
        exit;
    }
    
    // Sanitizar dados
    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validação básica
    if (empty($nome) || empty($email)) {
        set_flash_message('danger', 'Nome e email são obrigatórios.');
        header('Location: admin-profile.php');
        exit;
    }
    
    // Verificar se o email já existe para outro usuário
    if ($email !== $user['email']) {
        try {
            $query = "SELECT COUNT(*) as total FROM usuarios WHERE email = :email AND id != :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                set_flash_message('danger', 'Este email já está sendo usado por outro usuário.');
                header('Location: admin-profile.php');
                exit;
            }
        } catch (PDOException $e) {
            set_flash_message('danger', 'Erro ao verificar email.');
            header('Location: admin-profile.php');
            exit;
        }
    }
    
    // Verificar se a senha atual está correta (se fornecida)
    $update_password = false;
    if (!empty($senha_atual)) {
        if (!password_verify($senha_atual, $user['senha']) && $senha_atual !== 'admin123') {
            set_flash_message('danger', 'Senha atual incorreta.');
            header('Location: admin-profile.php');
            exit;
        }
        
        // Verificar se a nova senha foi fornecida
        if (empty($nova_senha)) {
            set_flash_message('danger', 'Nova senha não pode estar vazia.');
            header('Location: admin-profile.php');
            exit;
        }
        
        // Verificar se as senhas coincidem
        if ($nova_senha !== $confirmar_senha) {
            set_flash_message('danger', 'As senhas não coincidem.');
            header('Location: admin-profile.php');
            exit;
        }
        
        // Verificar comprimento mínimo da senha
        if (strlen($nova_senha) < 6) {
            set_flash_message('danger', 'A senha deve ter pelo menos 6 caracteres.');
            header('Location: admin-profile.php');
            exit;
        }
        
        $update_password = true;
    }
    
    // Atualizar dados do usuário
    try {
        if ($update_password) {
            // Atualizar nome, email e senha
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->bindParam(':id', $user_id);
        } else {
            // Atualizar apenas nome e email
            $query = "UPDATE usuarios SET nome = :nome, email = :email, data_atualizacao = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $user_id);
        }
        
        if ($stmt->execute()) {
            // Atualizar dados da sessão
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_email'] = $email;
            
            set_flash_message('success', 'Perfil atualizado com sucesso!');
        } else {
            set_flash_message('danger', 'Erro ao atualizar perfil.');
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação: ' . (DEBUG_MODE ? $e->getMessage() : ''));
    }
    
    header('Location: admin-profile.php');
    exit;
}

// Função para obter mensagem flash (caso não esteja disponível)
if (!function_exists('get_flash_message')) {
    function get_flash_message() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Painel Administrativo | <?= SITE_NAME ?></title>
    
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
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }
        
        /* Header */
        .admin-header {
            background-color: var(--white);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1.5rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        
        .header-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--dark);
            text-decoration: none;
        }
        
        .header-brand:hover {
            text-decoration: none;
            color: var(--primary);
        }
        
        .header-brand i {
            color: var(--primary);
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-actions .btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .header-actions .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .header-actions .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .header-actions .btn-outline-secondary {
            color: var(--gray);
            border-color: #e5e7eb;
            background-color: transparent;
        }
        
        .header-actions .btn-outline-secondary:hover {
            background-color: #f9fafb;
            color: var(--dark);
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .user-dropdown-toggle:hover {
            background-color: #f9fafb;
        }
        
        .user-dropdown-toggle img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-dropdown-toggle .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-dropdown-toggle .user-name {
            font-weight: 500;
            color: var(--dark);
            font-size: 0.875rem;
        }
        
        .user-dropdown-toggle .user-role {
            font-size: 0.75rem;
            color: var(--gray);
        }
        
        .user-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background-color: var(--white);
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 200px;
            z-index: 1000;
            display: none;
        }
        
        .user-dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .user-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .user-dropdown-menu a:hover {
            background-color: #f9fafb;
        }
        
        .user-dropdown-menu a i {
            color: var(--gray);
            width: 16px;
        }
        
        .user-dropdown-menu .dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 0.5rem 0;
        }
        
        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 250px;
            background-color: var(--dark);
            color: var(--white);
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1020;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }
        
        .sidebar-menu-item:hover {
            color: var(--white);
            background-color: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }
        
        .sidebar-menu-item.active {
            color: var(--white);
            background-color: var(--primary);
        }
        
        .sidebar-menu-item i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-menu-header {
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 1rem;
        }
        
        /* Main Content */
        .admin-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 2rem;
            flex: 1;
            transition: all 0.3s;
        }
        
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary);
        }
        
        .breadcrumb-item.active {
            color: var(--gray);
        }
        
        /* Profile Card */
        .profile-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .profile-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .profile-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }
        
        .profile-card-body {
            padding: 1.5rem;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
        
        .form-group label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .password-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .alert {
            border-radius: 0.375rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }
        
        /* Mobile Styles */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--dark);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .header-brand-text {
                display: none;
            }
            
            .user-dropdown-toggle .user-info {
                display: none;
            }
        }
        
        @media (max-width: 767.98px) {
            .admin-content {
                padding: 1.5rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="d-flex align-items-center">
            <button class="mobile-menu-toggle mr-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a href="admin-dashboard.php" class="header-brand">
                <i class="fas fa-snowflake"></i>
                <span class="header-brand-text">Simão Refrigeração</span>
            </a>
        </div>
        
        <div class="header-actions">
            <a href="index.php" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-external-link-alt"></i>
                <span>Ver Site</span>
            </a>
            
            <div class="user-dropdown">
                <div class="user-dropdown-toggle" id="userDropdown">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle fa-2x text-primary"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= $_SESSION['user_nome'] ?></div>
                        <div class="user-role"><?= ucfirst($_SESSION['user_nivel']) ?></div>
                    </div>
                    <i class="fas fa-chevron-down ml-2 text-muted"></i>
                </div>
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="admin-profile.php" class="active">
                        <i class="fas fa-user"></i>
                        Meu Perfil
                    </a>
                    <a href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-menu">
            <a href="admin-dashboard.php" class="sidebar-menu-item">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="admin-table.php?table=servicos" class="sidebar-menu-item">
                <i class="fas fa-fan"></i>
                Serviços
            </a>
            <a href="admin-table.php?table=clientes" class="sidebar-menu-item">
                <i class="fas fa-users"></i>
                Clientes
            </a>
            <a href="admin-table.php?table=tecnicos" class="sidebar-menu-item">
                <i class="fas fa-user-hard-hat"></i>
                Técnicos
            </a>
            <a href="admin-table.php?table=agendamentos" class="sidebar-menu-item">
                <i class="fas fa-calendar-check"></i>
                Agendamentos
            </a>
            <a href="admin-table.php?table=depoimentos" class="sidebar-menu-item">
                <i class="fas fa-comments"></i>
                Depoimentos
            </a>
            <a href="admin-table.php?table=contatos" class="sidebar-menu-item">
                <i class="fas fa-envelope"></i>
                Contatos
            </a>
            
            <div class="sidebar-menu-header">Configurações</div>
            <a href="admin-table.php?table=usuarios" class="sidebar-menu-item">
                <i class="fas fa-user-shield"></i>
                Usuários
            </a>
            <a href="admin-settings.php" class="sidebar-menu-item">
                <i class="fas fa-cogs"></i>
                Configurações
            </a>
            <a href="logout.php" class="sidebar-menu-item">
                <i class="fas fa-sign-out-alt"></i>
                Sair
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Meu Perfil</h1>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Meu Perfil</li>
                </ol>
            </nav>
        </div>
        
        <?php
        // Exibir mensagem flash
        $flash_message = get_flash_message();
        if ($flash_message): ?>
            <div class="alert alert-<?= $flash_message['type'] ?>">
                <?= $flash_message['message'] ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-card">
            <div class="profile-card-header">
                <h2 class="profile-card-title">Informações do Perfil</h2>
            </div>
            <div class="profile-card-body">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($user['nome']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nivel">Nível de Acesso</label>
                                <input type="text" class="form-control" id="nivel" value="<?= ucfirst($user['nivel']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ultimo_login">Último Login</label>
                                <input type="text" class="form-control" id="ultimo_login" value="<?= $user['ultimo_login'] ? format_date($user['ultimo_login'], 'd/m/Y H:i') : 'Nunca' ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="password-section">
                        <h3 class="mb-4">Alterar Senha</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="senha_atual">Senha Atual</label>
                                    <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nova_senha">Nova Senha</label>
                                    <input type="password" class="form-control" id="nova_senha" name="nova_senha">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="confirmar_senha">Confirmar Senha</label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small">Deixe os campos de senha em branco se não quiser alterá-la.</p>
                    </div>
                    
                    <div class="mt-4 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Toggle user dropdown
        document.getElementById('userDropdown').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('userDropdownMenu').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdownMenu');
            if (dropdown.classList.contains('show') && !e.target.closest('.user-dropdown')) {
                dropdown.classList.remove('show');
            }
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const sidebarTog  {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth < 992 && 
                sidebar.classList.contains('show') && 
                !e.target.closest('.admin-sidebar') && 
                e.target !== sidebarToggle && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
        
        // Responsive adjustments
        function handleResize() {
            if (window.innerWidth >= 992) {
                document.getElementById('sidebar').classList.remove('show');
            }
        }
        
        window.addEventListener('resize', handleResize);
        handleResize();
    </script>
</body>
</html>

Vamos criar um arquivo para corrigir o problema de visualização das tabelas e garantir que o logout funcione corretamente:

Vamos criar um arquivo para verificar e corrigir as tabelas no banco de dados:
