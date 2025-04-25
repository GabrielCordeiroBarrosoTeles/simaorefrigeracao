<?php
// Incluir arquivo de segurança
require_once 'admin-security.php';

// Conectar ao banco de dados
$db = db_connect();

// Obter estatísticas
$stats = [
    'clientes' => countRows($db, 'clientes'),
    'servicos' => countRows($db, 'servicos'),
    'agendamentos' => countRows($db, 'agendamentos'),
    'tecnicos' => countRows($db, 'tecnicos'),
    'contatos' => countRows($db, 'contatos'),
    'depoimentos' => countRows($db, 'depoimentos'),
    'usuarios' => countRows($db, 'usuarios')
];

// Função para contar registros
function countRows($db, $table) {
    try {
        // Verificar se a tabela existe
        $query = "SHOW TABLES LIKE :table";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':table', $table);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return 0;
        }
        
        $query = "SELECT COUNT(*) as total FROM $table";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// Obter tabelas do banco de dados
$tables = [];
try {
    $query = "SHOW TABLES";
    $stmt = $db->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
} catch (PDOException $e) {
    // Ignorar erro
}

// Obter atividades recentes
$recent_activities = [];
try {
    // Verificar se as tabelas necessárias existem
    $tables_exist = true;
    $required_tables = ['usuarios', 'contatos', 'agendamentos'];
    foreach ($required_tables as $table) {
        $query = "SHOW TABLES LIKE :table";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':table', $table);
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            $tables_exist = false;
            break;
        }
    }
    
    if ($tables_exist) {
        // Obter logins recentes
        $query = "SELECT 'login' as tipo, nome, ultimo_login as data 
                  FROM usuarios 
                  WHERE ultimo_login IS NOT NULL
                  ORDER BY ultimo_login DESC
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $logins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obter contatos recentes
        $query = "SELECT 'contato' as tipo, nome, data_criacao as data 
                  FROM contatos
                  ORDER BY data_criacao DESC
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obter agendamentos recentes
        $query = "SELECT 'agendamento' as tipo, titulo as nome, data_criacao as data 
                  FROM agendamentos
                  ORDER BY data_criacao DESC
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Combinar e ordenar por data
        $recent_activities = array_merge($logins, $contatos, $agendamentos);
        usort($recent_activities, function($a, $b) {
            return strtotime($b['data']) - strtotime($a['data']);
        });
        
        // Limitar a 10 atividades
        $recent_activities = array_slice($recent_activities, 0, 10);
    }
} catch (PDOException $e) {
    // Ignorar erro
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Painel Administrativo | <?= SITE_NAME ?></title>
    
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
        
        /* Activity Feed */
        .activity-feed {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .activity-feed-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .activity-feed-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .activity-feed-title i {
            color: var(--primary);
        }
        
        .activity-feed-body {
            padding: 1.25rem 1.5rem;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding-bottom: 1.25rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .activity-item:last-child {
            padding-bottom: 0;
            margin-bottom: 0;
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .activity-icon.login {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }
        
        .activity-icon.contato {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .activity-icon.agendamento {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--gray);
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
                    <a href="admin-profile.php">
                        <i class="fas fa-user"></i>
                        Meu Perfil
                    </a>
                    <a href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="admin-dashboard.php?logout=1">
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
            <a href="admin-dashboard.php" class="sidebar-menu-item active">
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
            <a href="admin-dashboard.php?logout=1" class="sidebar-menu-item">
                <i class="fas fa-sign-out-alt"></i>
                Sair
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>
        
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="stats-card">
                    <div class="stats-card-header">
                        <div class="stats-card-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stats-card-value"><?= $stats['clientes'] ?></div>
                    <div class="stats-card-label">Clientes</div>
                    <div class="stats-card-footer">
                        <a href="admin-table.php?table=clientes" class="stats-card-link">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="stats-card">
                    <div class="stats-card-header">
                        <div class="stats-card-icon success">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stats-card-value"><?= $stats['agendamentos'] ?></div>
                    <div class="stats-card-label">Agendamentos</div>
                    <div class="stats-card-footer">
                        <a href="admin-table.php?table=agendamentos" class="stats-card-link">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="stats-card">
                    <div class="stats-card-header">
                        <div class="stats-card-icon warning">
                            <i class="fas fa-user-hard-hat"></i>
                        </div>
                    </div>
                    <div class="stats-card-value"><?= $stats['tecnicos'] ?></div>
                    <div class="stats-card-label">Técnicos</div>
                    <div class="stats-card-footer">
                        <a href="admin-table.php?table=tecnicos" class="stats-card-link">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="stats-card">
                    <div class="stats-card-header">
                        <div class="stats-card-icon info">
                            <i class="fas fa-fan"></i>
                        </div>
                    </div>
                    <div class="stats-card-value"><?= $stats['servicos'] ?></div>
                    <div class="stats-card-label">Serviços</div>
                    <div class="stats-card-footer">
                        <a href="admin-table.php?table=servicos" class="stats-card-link">
                            Ver todos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Tables List -->
            <div class="col-lg-8 mb-4">
                <div class="data-table-card">
                    <div class="data-table-header">
                        <h2 class="data-table-title">
                            <i class="fas fa-database"></i>
                            Tabelas do Banco de Dados
                        </h2>
                    </div>
                    <div class="data-table-body">
                        <div class="data-table-responsive">
                            <table class="data-table table">
                                <thead>
                                    <tr>
                                        <th>Tabela</th>
                                        <th>Registros</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tables as $table): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $table ?></strong>
                                        </td>
                                        <td><?= $stats[$table] ?? countRows($db, $table) ?></td>
                                        <td class="text-center">
                                            <a href="admin-table.php?table=<?= $table ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Visualizar
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Feed -->
            <div class="col-lg-4 mb-4">
                <div class="activity-feed">
                    <div class="activity-feed-header">
                        <h2 class="activity-feed-title">
                            <i class="fas fa-history"></i>
                            Atividades Recentes
                        </h2>
                    </div>
                    <div class="activity-feed-body">
                        <?php if (empty($recent_activities)): ?>
                            <p class="text-center text-muted">Nenhuma atividade recente.</p>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon <?= $activity['tipo'] ?>">
                                        <?php if ($activity['tipo'] === 'login'): ?>
                                            <i class="fas fa-sign-in-alt"></i>
                                        <?php elseif ($activity['tipo'] === 'contato'): ?>
                                            <i class="fas fa-envelope"></i>
                                        <?php elseif ($activity['tipo'] === 'agendamento'): ?>
                                            <i class="fas fa-calendar-check"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?= $activity['nome'] ?></div>
                                        <div class="activity-time">
                                            <?= format_date($activity['data'], 'd/m/Y H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
