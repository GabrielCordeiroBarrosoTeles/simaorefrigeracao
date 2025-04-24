<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Atualizar o título da página admin -->
    <title>Painel Administrativo | <?= SITE_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="/admin" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="/" target="_blank" class="nav-link">Ver Site</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle"></i>
                        <?= $_SESSION['user_nome'] ?? 'Usuário' ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="/admin/perfil" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="/admin/logout" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Sair
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <!-- Atualizar o logo no sidebar -->
            <a href="/admin" class="brand-link">
                <i class="fas fa-snowflake brand-image"></i>
                <span class="brand-text font-weight-light">Simão Refrigeração</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="/admin" class="nav-link <?= $route === '/admin' || $route === '/admin/' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/servicos" class="nav-link <?= strpos($route, '/admin/servicos') === 0 ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-fan"></i>
                                <p>Serviços</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/depoimentos" class="nav-link <?= strpos($route, '/admin/depoimentos') === 0 ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-comments"></i>
                                <p>Depoimentos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/contatos" class="nav-link <?= strpos($route, '/admin/contatos') === 0 ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-envelope"></i>
                                <p>Contatos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/configuracoes" class="nav-link <?= strpos($route, '/admin/configuracoes') === 0 ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Configurações</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/admin/usuarios" class="nav-link <?= strpos($route, '/admin/usuarios') === 0 ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Usuários</p>
                            </a>
                        </li>
                        <li class="nav-header">CONTA</li>
                        <li class="nav-item">
                            <a href="/admin/logout" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Sair</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
