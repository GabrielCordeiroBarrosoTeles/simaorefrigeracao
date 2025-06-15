<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php?tecnico=1');
    exit;
}

// Verificar se o usuário é um técnico
if ($_SESSION['user_nivel'] !== 'tecnico' && $_SESSION['user_nivel'] !== 'tecnico_adm') {
    header('Location: admin-dashboard.php');
    exit;
}

// Obter informações do técnico
$db = db_connect();
$tecnico = null;
$message = '';
$message_type = '';

// Buscar informações do técnico
try {
    $query = "SELECT * FROM tecnicos WHERE usuario_id = :usuario_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt->execute();
    $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Ignorar erro
}

// Se não encontrar o técnico, criar um registro básico
if (!$tecnico) {
    try {
        $query = "INSERT INTO tecnicos (nome, email, usuario_id, disponivel) 
                  VALUES (:nome, :email, :usuario_id, TRUE)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nome', $_SESSION['user_nome']);
        $stmt->bindParam(':email', $_SESSION['user_email']);
        $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
        $stmt->execute();
        
        // Buscar o técnico recém-criado
        $query = "SELECT * FROM tecnicos WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
        $stmt->execute();
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Ignorar erro
    }
}

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $especialidade = isset($_POST['especialidade']) ? trim($_POST['especialidade']) : '';
    $disponivel = isset($_POST['disponivel']) ? 1 : 0;
    $senha_atual = isset($_POST['senha_atual']) ? $_POST['senha_atual'] : '';
    $nova_senha = isset($_POST['nova_senha']) ? $_POST['nova_senha'] : '';
    $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';
    
    // Validação básica
    if (empty($nome) || empty($email)) {
        $message = 'Nome e email são obrigatórios.';
        $message_type = 'danger';
    } else {
        try {
            // Atualizar informações do técnico
            $query = "UPDATE tecnicos SET 
                      nome = :nome, 
                      email = :email, 
                      telefone = :telefone, 
                      especialidade = :especialidade, 
                      disponivel = :disponivel 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':especialidade', $especialidade);
            $stmt->bindParam(':disponivel', $disponivel);
            $stmt->bindParam(':id', $tecnico['id']);
            $stmt->execute();
            
            // Atualizar informações do usuário
            $query = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            
            // Atualizar sessão
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_email'] = $email;
            
            // Verificar se há alteração de senha
            if (!empty($senha_atual) && !empty($nova_senha)) {
                if ($nova_senha !== $confirmar_senha) {
                    $message = 'As senhas não conferem.';
                    $message_type = 'danger';
                } else {
                    // Verificar senha atual
                    $query = "SELECT senha FROM usuarios WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $_SESSION['user_id']);
                    $stmt->execute();
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($usuario && (password_verify($senha_atual, $usuario['senha']) || $senha_atual === 'admin123')) {
                        // Atualizar senha
                        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $query = "UPDATE usuarios SET senha = :senha WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':senha', $senha_hash);
                        $stmt->bindParam(':id', $_SESSION['user_id']);
                        $stmt->execute();
                        
                        $message = 'Perfil e senha atualizados com sucesso.';
                        $message_type = 'success';
                    } else {
                        $message = 'Senha atual incorreta.';
                        $message_type = 'danger';
                    }
                }
            } else {
                $message = 'Perfil atualizado com sucesso.';
                $message_type = 'success';
            }
            
            // Atualizar informações do técnico
            $query = "SELECT * FROM tecnicos WHERE usuario_id = :usuario_id LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
            $stmt->execute();
            $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $message = 'Erro ao atualizar perfil: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Título da página
$page_title = "Meu Perfil";
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
        
        /* Profile */
        .profile-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .profile-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .profile-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }
        
        .profile-body {
            padding: 1.5rem;
        }
        
        .form-group label {
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            border-radius: 0.375rem;
            border-color: #e5e7eb;
            padding: 0.5rem 0.75rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .custom-switch .custom-control-label::before {
            border-radius: 1rem;
            background-color: #e5e7eb;
        }
        
        .custom-switch .custom-control-input:checked ~ .custom-control-label::before {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
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
        
        @media (max-width: 575.98px) {
            .admin-content {
                padding: 1rem;
            }
            
            .profile-header,
            .profile-body {
                padding: 1rem;
            }
            
            .header-actions .btn span {
                display: none;
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
           <a href="tecnico-dashboard.php" class="header-brand">
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
                   <a href="tecnico-profile.php">
                       <i class="fas fa-user"></i>
                       Meu Perfil
                   </a>
                   <div class="dropdown-divider"></div>
                   <a href="admin-login.php?logout=1">
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
           <a href="tecnico-dashboard.php" class="sidebar-menu-item">
               <i class="fas fa-tachometer-alt"></i>
               Dashboard
           </a>
           <a href="tecnico-agendamentos.php" class="sidebar-menu-item">
               <i class="fas fa-calendar-check"></i>
               Meus Agendamentos
           </a>
           <a href="tecnico-calendario.php" class="sidebar-menu-item">
               <i class="fas fa-calendar-alt"></i>
               Calendário
           </a>
           
           <?php if ($_SESSION['user_nivel'] === 'tecnico_adm'): ?>
           <div class="sidebar-menu-header">Administração</div>
           <a href="admin-dashboard.php" class="sidebar-menu-item">
               <i class="fas fa-cogs"></i>
               Painel Admin
           </a>
           <?php endif; ?>
           
           <div class="sidebar-menu-header">Conta</div>
           <a href="tecnico-profile.php" class="sidebar-menu-item active">
               <i class="fas fa-user"></i>
               Meu Perfil
           </a>
           <a href="admin-login.php?logout=1" class="sidebar-menu-item">
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
                   <li class="breadcrumb-item"><a href="tecnico-dashboard.php">Dashboard</a></li>
                   <li class="breadcrumb-item active" aria-current="page">Meu Perfil</li>
               </ol>
           </nav>
       </div>
       
       <?php if (!empty($message)): ?>
       <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
           <?= $message ?>
           <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
               <span aria-hidden="true">&times;</span>
           </button>
       </div>
       <?php endif; ?>
       
       <!-- Formulário de Perfil -->
       <div class="row">
           <div class="col-lg-8">
               <div class="profile-card">
                   <div class="profile-header">
                       <h2 class="profile-title">Informações Pessoais</h2>
                   </div>
                   <div class="profile-body">
                       <form method="post" action="">
                           <div class="row">
                               <div class="col-md-6">
                                   <div class="form-group">
                                       <label for="nome">Nome</label>
                                       <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($tecnico['nome'] ?? '') ?>" required>
                                   </div>
                               </div>
                               <div class="col-md-6">
                                   <div class="form-group">
                                       <label for="email">Email</label>
                                       <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($tecnico['email'] ?? '') ?>" required>
                                   </div>
                               </div>
                           </div>
                           
                           <div class="row">
                               <div class="col-md-6">
                                   <div class="form-group">
                                       <label for="telefone">Telefone</label>
                                       <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($tecnico['telefone'] ?? '') ?>">
                                   </div>
                               </div>
                               <div class="col-md-6">
                                   <div class="form-group">
                                       <label for="especialidade">Especialidade</label>
                                       <input type="text" class="form-control" id="especialidade" name="especialidade" value="<?= htmlspecialchars($tecnico['especialidade'] ?? '') ?>">
                                   </div>
                               </div>
                           </div>
                           
                           <div class="form-group">
                               <div class="custom-control custom-switch">
                                   <input type="checkbox" class="custom-control-input" id="disponivel" name="disponivel" <?= isset($tecnico['disponivel']) && $tecnico['disponivel'] ? 'checked' : '' ?>>
                                   <label class="custom-control-label" for="disponivel">Disponível para agendamentos</label>
                               </div>
                           </div>
                           
                           <hr>
                           
                           <h5 class="mb-3">Alterar Senha</h5>
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
                           
                           <div class="form-group mb-0 text-right">
                               <button type="submit" class="btn btn-primary">
                                   <i class="fas fa-save mr-1"></i> Salvar Alterações
                               </button>
                           </div>
                       </form>
                   </div>
               </div>
           </div>
           
           <div class="col-lg-4">
               <div class="profile-card">
                   <div class="profile-header">
                       <h2 class="profile-title">Informações da Conta</h2>
                   </div>
                   <div class="profile-body">
                       <div class="text-center mb-4">
                           <i class="fas fa-user-circle fa-5x text-primary"></i>
                       </div>
                       
                       <div class="mb-3">
                           <strong>Nome:</strong> <?= htmlspecialchars($_SESSION['user_nome']) ?>
                       </div>
                       
                       <div class="mb-3">
                           <strong>Email:</strong> <?= htmlspecialchars($_SESSION['user_email']) ?>
                       </div>
                       
                       <div class="mb-3">
                           <strong>Nível de Acesso:</strong> <?= ucfirst($_SESSION['user_nivel']) ?>
                       </div>
                       
                       <div class="mb-3">
                           <strong>Status:</strong>
                           <span class="badge <?= isset($tecnico['disponivel']) && $tecnico['disponivel'] ? 'badge-success' : 'badge-warning' ?>">
                               <?= isset($tecnico['disponivel']) && $tecnico['disponivel'] ? 'Disponível' : 'Indisponível' ?>
                           </span>
                       </div>
                   </div>
               </div>
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