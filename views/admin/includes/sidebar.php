<div class="sidebar">
    <div class="sidebar-header">
        <a href="admin-dashboard.php" class="sidebar-brand">
            <i class="fas fa-snowflake text-primary"></i>
            <span>Simão Refrigeração</span>
        </a>
        <button id="sidebarToggle" class="btn btn-link d-md-none rounded-circle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : '' ?>" href="admin-dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <div class="sidebar-heading">Cadastros</div>
            
            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin-table.php' && isset($_GET['table']) && $_GET['table'] == 'clientes') ? 'active' : '' ?>" href="admin-table.php?table=clientes">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin-table.php' && isset($_GET['table']) && $_GET['table'] == 'tecnicos') ? 'active' : '' ?>" href="admin-table.php?table=tecnicos">
                    <i class="fas fa-user-hard-hat"></i>
                    <span>Técnicos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin-table.php' && isset($_GET['table']) && $_GET['table'] == 'servicos') ? 'active' : '' ?>" href="admin-table.php?table=servicos">
                    <i class="fas fa-tools"></i>
                    <span>Serviços</span>
                </a>
            </li>
            
            <div class="sidebar-heading">Operacional</div>
            
            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin-table.php' && isset($_GET['table']) && $_GET['table'] == 'agendamentos') ? 'active' : '' ?>" href="admin-table.php?table=agendamentos">
                    <i class="fas fa-calendar-check"></i>
                    <span>Agendamentos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-calendario.php' ? 'active' : '' ?>" href="admin-calendario.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Calendário</span>
                </a>
            </li>
            
            <div class="sidebar-heading">Site</div>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-depoimentos.php' ? 'active' : '' ?>" href="admin-depoimentos.php">
                    <i class="fas fa-comments"></i>
                    <span>Depoimentos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-estatisticas.php' ? 'active' : '' ?>" href="admin-estatisticas.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Estatísticas</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-contatos.php' ? 'active' : '' ?>" href="admin-contatos.php">
                    <i class="fas fa-envelope"></i>
                    <span>Contatos</span>
                </a>
            </li>
            
            <div class="sidebar-heading">Sistema</div>
            
            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin-table.php' && isset($_GET['table']) && $_GET['table'] == 'usuarios') ? 'active' : '' ?>" href="admin-table.php?table=usuarios">
                    <i class="fas fa-user-shield"></i>
                    <span>Usuários</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin-settings.php' ? 'active' : '' ?>" href="admin-settings.php">
                    <i class="fas fa-cogs"></i>
                    <span>Configurações</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </div>
</div>
