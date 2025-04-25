<?php
// Incluir arquivo de segurança
require_once 'admin-security.php';

// Verificar se a tabela foi especificada
if (!isset($_GET['table']) || empty($_GET['table'])) {
    header('Location: admin-dashboard.php');
    exit;
}

$table = $_GET['table'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Conectar ao banco de dados
$db = db_connect();

// Verificar se a tabela existe
try {
    $query = "SHOW TABLES LIKE :table";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':table', $table);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('Location: admin-dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: admin-dashboard.php');
    exit;
}

// Obter colunas da tabela
$columns = [];
try {
    $query = "SHOW COLUMNS FROM $table";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Ignorar erro
}

// Obter total de registros
$total = 0;
try {
    $query = "SELECT COUNT(*) as total FROM $table";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $result['total'] ?? 0;
} catch (PDOException $e) {
    // Ignorar erro
}

// Calcular total de páginas
$total_pages = ceil($total / $limit);

// Obter registros
$records = [];
try {
    $query = "SELECT * FROM $table LIMIT :offset, :limit";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Ignorar erro
}

// Processar exclusão de registro
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $query = "DELETE FROM $table WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $message = "Registro excluído com sucesso!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Erro ao excluir registro: " . $e->getMessage();
        $message_type = "danger";
    }
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: admin-table.php?table=$table&message=$message&type=$message_type");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabela <?= ucfirst($table) ?> - Painel Administrativo | <?= SITE_NAME ?></title>
    
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
        
        /* Table Card */
        .table-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .table-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .table-card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .table-card-title i {
            color: var(--primary);
        }
        
        .table-card-body {
            padding: 0;
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .data-table th {
            font-weight: 600;
            background-color: #f9fafb;
            color: var(--dark);
            padding: 0.75rem 1rem;
            border-top: none;
            white-space: nowrap;
        }
        
        .data-table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-top: 1px solid #e5e7eb;
        }
        
        .data-table .actions {
            white-space: nowrap;
        }
        
        .data-table .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .table-card-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .pagination .page-link {
            color: var(--primary);
            border-color: #e5e7eb;
            margin: 0 0.125rem;
            border-radius: 0.25rem;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .pagination .page-item.disabled .page-link {
            color: var(--gray);
        }
        
        .alert {
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                padding: 1.5rem 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .table-card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .table-card-footer {
                flex-direction: column;
                gap: 1rem;
            }
            
            .pagination {
                margin-top: 1rem;
            }
            
            .data-table td, 
            .data-table th {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
            
            .data-table .actions .btn {
                padding: 0.25rem 0.4rem;
                font-size: 0.75rem;
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
            <a href="/" target="_blank" class="btn btn-outline-secondary">
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
            <a href="admin-dashboard.php" class="sidebar-menu-item">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="admin-table.php?table=servicos" class="sidebar-menu-item <?= $table === 'servicos' ? 'active' : '' ?>">
                <i class="fas fa-fan"></i>
                Serviços
            </a>
            <a href="admin-table.php?table=clientes" class="sidebar-menu-item <?= $table === 'clientes' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                Clientes
            </a>
            <a href="admin-table.php?table=tecnicos" class="sidebar-menu-item <?= $table === 'tecnicos' ? 'active' : '' ?>">
                <i class="fas fa-user-hard-hat"></i>
                Técnicos
            </a>
            <a href="admin-table.php?table=agendamentos" class="sidebar-menu-item <?= $table === 'agendamentos' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                Agendamentos
            </a>
            <a href="admin-table.php?table=depoimentos" class="sidebar-menu-item <?= $table === 'depoimentos' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i>
                Depoimentos
            </a>
            <a href="admin-table.php?table=contatos" class="sidebar-menu-item <?= $table === 'contatos' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i>
                Contatos
            </a>
            
            <div class="sidebar-menu-header">Configurações</div>
            <a href="admin-table.php?table=usuarios" class="sidebar-menu-item <?= $table === 'usuarios' ? 'active' : '' ?>">
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
            <h1 class="page-title">Tabela: <?= ucfirst($table) ?></h1>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= ucfirst($table) ?></li>
                </ol>
            </nav>
        </div>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-<?= $_GET['type'] ?? 'info' ?>">
                <?= $_GET['message'] ?>
            </div>
        <?php endif; ?>
        
        <div class="table-card">
            <div class="table-card-header">
                <h2 class="table-card-title">
                    <i class="fas fa-table"></i>
                    Registros de <?= ucfirst($table) ?>
                </h2>
                
                <div>
                    <a href="admin-record.php?table=<?= $table ?>&action=new" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Registro
                    </a>
                </div>
            </div>
            
            <div class="table-card-body">
                <?php if (empty($records)): ?>
                    <div class="p-4 text-center text-muted">
                        Nenhum registro encontrado.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table table">
                            <thead>
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <th><?= ucfirst($column) ?></th>
                                    <?php endforeach; ?>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <td>
                                                <?php 
                                                $value = $record[$column] ?? '';
                                                if (is_string($value) && strlen($value) > 100) {
                                                    echo '<div class="truncate" title="' . htmlspecialchars($value) . '">' . htmlspecialchars(substr($value, 0, 100)) . '...</div>';
                                                } elseif ($column === 'senha') {
                                                    echo '********';
                                                } else {
                                                    echo htmlspecialchars($value);
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="text-center actions">
                                            <?php if (isset($record['id'])): ?>
                                                <a href="admin-record.php?table=<?= $table ?>&id=<?= $record['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="admin-record.php?table=<?= $table ?>&id=<?= $record['id'] ?>&edit=1" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="admin-table.php?table=<?= $table ?>&delete=1&id=<?= $record['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="table-card-footer">
                    <div>
                        <span class="text-muted">Mostrando <?= count($records) ?> de <?= $total ?> registros</span>
                    </div>
                    
                    <nav aria-label="Paginação">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="admin-table.php?table=<?= $table ?>&page=<?= $page - 1 ?>" aria-label="Anterior">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">&laquo;</span>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="admin-table.php?table=<?= $table ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="admin-table.php?table=<?= $table ?>&page=<?= $page + 1 ?>" aria-label="Próximo">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">&raquo;</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
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
