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

// Verificar se o usuário é um técnico
if ($_SESSION['user_nivel'] !== 'tecnico' && $_SESSION['user_nivel'] !== 'tecnico_adm') {
    header('Location: admin-dashboard.php');
    exit;
}

// Obter informações do técnico
$db = db_connect();
$tecnico = null;

// Função para verificar se uma tabela existe
function table_exists($db, $table) {
    try {
        $result = $db->query("SHOW TABLES LIKE '{$table}'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Buscar informações do técnico
if (table_exists($db, 'tecnicos')) {
    try {
        $query = "SELECT * FROM tecnicos WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
        $stmt->execute();
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tecnico) {
            error_log("Técnico não encontrado para o usuário ID: " . $_SESSION['user_id']);
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar informações do técnico: " . $e->getMessage());
    }
}

// Se não encontrar o técnico, criar um registro básico
if (!$tecnico && table_exists($db, 'tecnicos')) {
    try {
        error_log("Criando novo registro de técnico para o usuário ID: " . $_SESSION['user_id']);
        
        // Definir valores padrão
        $nome = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'Técnico';
        $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'tecnico@simaorefrigeracao.com';
        $disponivel = true;
        
        $query = "INSERT INTO tecnicos (nome, email, usuario_id, disponivel) 
                  VALUES (:nome, :email, :usuario_id, :disponivel)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
        $stmt->bindParam(':disponivel', $disponivel, PDO::PARAM_BOOL);
        $stmt->execute();
        
        // Obter o ID do técnico recém-inserido
        $tecnico_id = $db->lastInsertId();
        error_log("Novo técnico criado com ID: " . $tecnico_id);
        
        // Criar um array com os dados do técnico para uso imediato
        $tecnico = [
            'id' => $tecnico_id,
            'nome' => $nome,
            'email' => $email,
            'usuario_id' => $_SESSION['user_id'],
            'disponivel' => $disponivel
        ];
        
        error_log("Técnico criado com sucesso: " . print_r($tecnico, true));
    } catch (Exception $e) {
        error_log("Erro ao criar novo registro de técnico: " . $e->getMessage());
    }
}

// Obter agendamentos do técnico
$agendamentos = [];
if ($tecnico && table_exists($db, 'agendamentos')) {
    try {
        $query = "SELECT a.*, 
                 IFNULL((SELECT nome FROM clientes WHERE id = a.cliente_id), 'Cliente não encontrado') as cliente_nome,
                 IFNULL((SELECT telefone FROM clientes WHERE id = a.cliente_id), '') as cliente_telefone,
                 IFNULL((SELECT nome FROM servicos WHERE id = a.servico_id), 'Serviço não encontrado') as servico_nome
                 FROM agendamentos a 
                 WHERE a.tecnico_id = :tecnico_id
                 ORDER BY a.data_agendamento DESC 
                 LIMIT 10";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tecnico_id', $tecnico['id']);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar se há resultados
        if (empty($agendamentos)) {
            error_log("Nenhum agendamento encontrado para o técnico ID: " . $tecnico['id']);
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar agendamentos: " . $e->getMessage());
    }
}

// Obter agendamentos de hoje
$agendamentos_hoje = [];
if ($tecnico && table_exists($db, 'agendamentos')) {
    try {
        $hoje = date('Y-m-d');
        $query = "SELECT a.*, 
                 IFNULL((SELECT nome FROM clientes WHERE id = a.cliente_id), 'Cliente não encontrado') as cliente_nome,
                 IFNULL((SELECT telefone FROM clientes WHERE id = a.cliente_id), '') as cliente_telefone,
                 IFNULL((SELECT nome FROM servicos WHERE id = a.servico_id), 'Serviço não encontrado') as servico_nome
                 FROM agendamentos a 
                 WHERE a.tecnico_id = :tecnico_id 
                 AND DATE(a.data_agendamento) = :hoje
                 ORDER BY a.data_agendamento ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tecnico_id', $tecnico['id']);
        $stmt->bindParam(':hoje', $hoje);
        $stmt->execute();
        $agendamentos_hoje = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar se há resultados
        if (empty($agendamentos_hoje)) {
            error_log("Nenhum agendamento para hoje encontrado para o técnico ID: " . $tecnico['id']);
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar agendamentos de hoje: " . $e->getMessage());
    }
}

// Obter agendamentos pendentes
$agendamentos_pendentes = [];
if ($tecnico && table_exists($db, 'agendamentos')) {
    try {
        $hoje = date('Y-m-d');
        $query = "SELECT a.*, 
                 IFNULL((SELECT nome FROM clientes WHERE id = a.cliente_id), 'Cliente não encontrado') as cliente_nome,
                 IFNULL((SELECT telefone FROM clientes WHERE id = a.cliente_id), '') as cliente_telefone,
                 IFNULL((SELECT nome FROM servicos WHERE id = a.servico_id), 'Serviço não encontrado') as servico_nome
                 FROM agendamentos a 
                 WHERE a.tecnico_id = :tecnico_id 
                 AND a.status = 'pendente'
                 AND DATE(a.data_agendamento) >= :hoje
                 ORDER BY a.data_agendamento ASC
                 LIMIT 10";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tecnico_id', $tecnico['id']);
        $stmt->bindParam(':hoje', $hoje);
        $stmt->execute();
        $agendamentos_pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar se há resultados
        if (empty($agendamentos_pendentes)) {
            error_log("Nenhum agendamento pendente encontrado para o técnico ID: " . $tecnico['id']);
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar agendamentos pendentes: " . $e->getMessage());
    }
}

// Função para truncar texto
function truncate_tecnico($text, $length) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Técnico | <?= defined('SITE_NAME') ? SITE_NAME : 'Simão Refrigeração' ?></title>
    
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
        
        /* Cards */
        .stats-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            height: 100%;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .stats-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .stats-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stats-card-icon.primary {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }
        
        .stats-card-icon.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .stats-card-icon.warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .stats-card-icon.info {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--info);
        }
        
        .stats-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .stats-card-label {
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        .stats-card-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stats-card-link {
            color: var(--primary);
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            text-decoration: none;
        }
        
        .stats-card-link:hover {
            color: var(--primary-dark);
            text-decoration: none;
        }
        
        /* Tables */
        .data-table-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .data-table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .data-table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .data-table-title i {
            color: var(--primary);
        }
        
        .data-table-body {
            padding: 0;
        }
        
        .data-table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .data-table th {
            font-weight: 600;
            background-color: #f9fafb;
            color: var(--dark);
            padding: 0.75rem 1.5rem;
            border-top: none;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-top: 1px solid #e5e7eb;
        }
        
        .data-table-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
            border-radius: 0.25rem;
        }
        
        .badge-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .badge-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .badge-danger {
            background-color: rgba(239, 68, 68, 0.1);
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
            
            .stats-card {
                margin-bottom: 1.5rem;
            }
            
            .data-table-responsive {
                overflow-x: auto;
            }
            
            .data-table th, 
            .data-table td {
                white-space: nowrap;
            }
        }
        
        @media (max-width: 575.98px) {
            .admin-content {
                padding: 1rem;
            }
            
            .stats-card {
                padding: 1rem;
            }
            
            .stats-card-value {
                font-size: 1.5rem;
            }
            
            .data-table-header,
            .data-table-footer {
                padding: 1rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.75rem 1rem;
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
                   <a href="logout.php" id="logoutLink">
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
           <a href="tecnico-dashboard.php" class="sidebar-menu-item active">
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
           <a href="tecnico-profile.php" class="sidebar-menu-item">
               <i class="fas fa-user"></i>
               Meu Perfil
           </a>
           <a href="logout.php" class="sidebar-menu-item" id="sidebarLogoutLink">
               <i class="fas fa-sign-out-alt"></i>
               Sair
           </a>
       </div>
   </aside>
   
   <!-- Main Content -->
   <main class="admin-content">
       <div class="page-header">
           <h1 class="page-title">Dashboard do Técnico</h1>
           
           <nav aria-label="breadcrumb">
               <ol class="breadcrumb">
                   <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
               </ol>
           </nav>
       </div>
       
       <!-- Perfil do Técnico -->
       <div class="row mb-4">
           <div class="col-md-12">
               <div class="stats-card">
                   <div class="d-flex align-items-center">
                       <div class="mr-4">
                           <i class="fas fa-user-circle fa-4x text-primary"></i>
                       </div>
                       <div>
                           <h2 class="mb-1"><?= htmlspecialchars($_SESSION['user_nome']) ?></h2>
                           <p class="text-muted mb-2"><?= htmlspecialchars($_SESSION['user_email']) ?></p>
                           <p class="mb-0">
                               <span class="badge <?= $tecnico && isset($tecnico['disponivel']) && $tecnico['disponivel'] ? 'badge-success' : 'badge-warning' ?>">
                                   <?= $tecnico && isset($tecnico['disponivel']) && $tecnico['disponivel'] ? 'Disponível' : 'Indisponível' ?>
                               </span>
                           </p>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <!-- Stats Cards -->
       <div class="row">
           <div class="col-md-6 col-lg-3 mb-4">
               <div class="stats-card">
                   <div class="stats-card-header">
                       <div class="stats-card-icon primary">
                           <i class="fas fa-calendar-check"></i>
                       </div>
                   </div>
                   <div class="stats-card-value" id="total-agendamentos"><?= count($agendamentos) ?></div>
                   <div class="stats-card-label">Total Agendamentos</div>
                   <div class="stats-card-footer">
                       <a href="tecnico-agendamentos.php" class="stats-card-link">
                           Ver todos <i class="fas fa-arrow-right"></i>
                       </a>
                   </div>
               </div>
           </div>
           
           <div class="col-md-6 col-lg-3 mb-4">
               <div class="stats-card">
                   <div class="stats-card-header">
                       <div class="stats-card-icon success">
                           <i class="fas fa-check-circle"></i>
                       </div>
                   </div>
                   <div class="stats-card-value" id="total-concluidos">
                       <?php
                       $concluidos = 0;
                       foreach ($agendamentos as $agendamento) {
                           if (isset($agendamento['status']) && $agendamento['status'] === 'concluido') {
                               $concluidos++;
                           }
                       }
                       echo $concluidos;
                       ?>
                   </div>
                   <div class="stats-card-label">Concluídos</div>
                   <div class="stats-card-footer">
                       <a href="tecnico-agendamentos.php?status=concluido" class="stats-card-link">
                           Ver todos <i class="fas fa-arrow-right"></i>
                       </a>
                   </div>
               </div>
           </div>
           
           <div class="col-md-6 col-lg-3 mb-4">
               <div class="stats-card">
                   <div class="stats-card-header">
                       <div class="stats-card-icon warning">
                           <i class="fas fa-clock"></i>
                       </div>
                   </div>
                   <div class="stats-card-value" id="total-pendentes">
                       <?php
                       $pendentes = 0;
                       foreach ($agendamentos as $agendamento) {
                           if (isset($agendamento['status']) && $agendamento['status'] === 'pendente') {
                               $pendentes++;
                           }
                       }
                       echo $pendentes;
                       ?>
                   </div>
                   <div class="stats-card-label">Pendentes</div>
                   <div class="stats-card-footer">
                       <a href="tecnico-agendamentos.php?status=pendente" class="stats-card-link">
                           Ver todos <i class="fas fa-arrow-right"></i>
                       </a>
                   </div>
               </div>
           </div>
           
           <div class="col-md-6 col-lg-3 mb-4">
               <div class="stats-card">
                   <div class="stats-card-header">
                       <div class="stats-card-icon info">
                           <i class="fas fa-calendar-day"></i>
                       </div>
                   </div>
                   <div class="stats-card-value"><?= count($agendamentos_hoje) ?></div>
                   <div class="stats-card-label">Hoje</div>
                   <div class="stats-card-footer">
                       <a href="#agendamentos-hoje" class="stats-card-link">
                           Ver abaixo <i class="fas fa-arrow-down"></i>
                       </a>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="row">
           <!-- Agendamentos de Hoje -->
           <div class="col-lg-12 mb-4">
               <div class="data-table-card">
                   <div class="data-table-header">
                       <h2 class="data-table-title">
                           <i class="fas fa-calendar-day"></i>
                           Agendamentos de Hoje (<?= date('d/m/Y') ?>)
                       </h2>
                   </div>
                   <div class="data-table-body">
                       <div class="data-table-responsive">
                           <table class="data-table table">
                               <thead>
                                   <tr>
                                       <th>Horário</th>
                                       <th>Cliente</th>
                                       <th>Serviço</th>
                                       <th>Status</th>
                                       <th class="text-center">Ações</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php if (empty($agendamentos_hoje)): ?>
                                   <tr>
                                       <td colspan="5" class="text-center">Nenhum agendamento para hoje.</td>
                                   </tr>
                                   <?php else: ?>
                                       <?php foreach ($agendamentos_hoje as $agendamento): ?>
                                       <tr>
                                           <td>
                                               <?= isset($agendamento['data_agendamento']) ? format_date($agendamento['data_agendamento'], 'H:i') : 'N/A' ?>
                                           </td>
                                           <td>
                                               <strong><?= htmlspecialchars($agendamento['cliente_nome']) ?></strong>
                                               <?php if (!empty($agendamento['cliente_telefone'])): ?>
                                               <br><small><?= htmlspecialchars($agendamento['cliente_telefone']) ?></small>
                                               <?php endif; ?>
                                           </td>
                                           <td><?= htmlspecialchars($agendamento['servico_nome']) ?></td>
                                           <td>
                                               <?php
                                               $status_class = 'badge badge-info';
                                               $status_text = 'Em andamento';
                                               
                                               if (isset($agendamento['status'])) {
                                                   switch ($agendamento['status']) {
                                                       case 'pendente':
                                                       case 'p':
                                                           $status_class = 'badge badge-warning';
                                                           $status_text = 'Pendente';
                                                           break;
                                                       case 'em_andamento':
                                                       case 'a':
                                                           $status_class = 'badge badge-info';
                                                           $status_text = 'Em andamento';
                                                           break;
                                                       case 'concluido':
                                                       case 'c':
                                                           $status_class = 'badge badge-success';
                                                           $status_text = 'Concluído';
                                                           break;
                                                       case 'cancelado':
                                                       case 'x':
                                                           $status_class = 'badge badge-danger';
                                                           $status_text = 'Cancelado';
                                                           break;
                                                   }
                                               }
                                               ?>
                                               <span class="<?= $status_class ?>"><?= $status_text ?></span>
                                           </td>
                                           <td class="text-center">
                                               <a href="tecnico-agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-info">
                                                   <i class="fas fa-eye"></i>
                                               </a>
                                           </td>
                                       </tr>
                                       <?php endforeach; ?>
                                   <?php endif; ?>
                               </tbody>
                           </table>
                       </div>
                   </div>
               </div>
           </div>
           
           <!-- Próximos Agendamentos -->
           <div class="col-lg-12 mb-4">
               <div class="data-table-card">
                   <div class="data-table-header">
                       <h2 class="data-table-title">
                           <i class="fas fa-calendar-alt"></i>
                           Próximos Agendamentos
                       </h2>
                   </div>
                   <div class="data-table-body">
                       <div class="data-table-responsive">
                           <table class="data-table table">
                               <thead>
                                   <tr>
                                       <th>Data</th>
                                       <th>Cliente</th>
                                       <th>Serviço</th>
                                       <th>Status</th>
                                       <th class="text-center">Ações</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <?php if (empty($agendamentos_pendentes)): ?>
                                   <tr>
                                       <td colspan="5" class="text-center">Nenhum agendamento pendente.</td>
                                   </tr>
                                   <?php else: ?>
                                       <?php 
                                       $count = 0;
                                       foreach ($agendamentos_pendentes as $agendamento): 
                                           if ($count >= 5) break; // Limitar a 5 agendamentos
                                           $count++;
                                       ?>
                                       <tr>
                                           <td><?= isset($agendamento['data_agendamento']) ? format_date($agendamento['data_agendamento'], 'd/m/Y H:i') : 'N/A' ?></td>
                                           <td>
                                               <strong><?= htmlspecialchars($agendamento['cliente_nome']) ?></strong>
                                               <?php if (!empty($agendamento['cliente_telefone'])): ?>
                                               <br><small><?= htmlspecialchars($agendamento['cliente_telefone']) ?></small>
                                               <?php endif; ?>
                                           </td>
                                           <td><?= htmlspecialchars($agendamento['servico_nome']) ?></td>
                                           <td>
                                               <span class="badge badge-warning">Pendente</span>
                                           </td>
                                           <td class="text-center">
                                               <a href="tecnico-agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-info">
                                                   <i class="fas fa-eye"></i>
                                               </a>
                                           </td>
                                       </tr>
                                       <?php endforeach; ?>
                                   <?php endif; ?>
                               </tbody>
                           </table>
                       </div>
                   </div>
                   <div class="data-table-footer">
                       <a href="tecnico-agendamentos.php" class="btn btn-sm btn-primary">Ver Todos os Agendamentos</a>
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
           e.preventDefault();
           e.stopPropagation();
           document.getElementById('userDropdownMenu').classList.toggle('show');
       });
       
       // Adicionar evento de clique aos links de logout
       document.getElementById('logoutLink').addEventListener('click', function(e) {
           e.preventDefault();
           window.location.href = 'logout.php';
       });
       
       document.getElementById('sidebarLogoutLink').addEventListener('click', function(e) {
           e.preventDefault();
           window.location.href = 'logout.php';
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
       
       // Função para atualizar estatísticas
       function atualizarEstatisticas() {
           try {
               // Usando os dados já disponíveis na página
               const agendamentos = <?= json_encode($agendamentos) ?>;
               const agendamentosHoje = <?= json_encode($agendamentos_hoje) ?>;
               const agendamentosPendentes = <?= json_encode($agendamentos_pendentes) ?>;
               
               console.log("Dados carregados:", {
                   agendamentos: agendamentos,
                   agendamentosHoje: agendamentosHoje,
                   agendamentosPendentes: agendamentosPendentes
               });
               
               let total = agendamentos ? agendamentos.length : 0;
               let concluidos = 0;
               let pendentes = 0;
               
               if (agendamentos && agendamentos.length > 0) {
                   agendamentos.forEach(function(agendamento) {
                       if (agendamento && agendamento.status === 'concluido') {
                           concluidos++;
                       } else if (agendamento && agendamento.status === 'pendente') {
                           pendentes++;
                       }
                   });
               }
               
               // Verificar se os elementos existem antes de atualizar
               const totalElement = document.getElementById('total-agendamentos');
               const concluidosElement = document.getElementById('total-concluidos');
               const pendentesElement = document.getElementById('total-pendentes');
               
               if (totalElement) totalElement.textContent = total;
               if (concluidosElement) concluidosElement.textContent = concluidos;
               if (pendentesElement) pendentesElement.textContent = pendentes;
           } catch (error) {
               console.error("Erro ao atualizar estatísticas:", error);
           }
       }
       
       // Executar quando o DOM estiver carregado
       document.addEventListener('DOMContentLoaded', function() {
           // Atualizar estatísticas
           atualizarEstatisticas();
       });
   </script>
</body>
</html>
