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

// Obter agendamentos do técnico
$agendamentos = [];
if ($tecnico) {
    try {
        // Filtrar por status se especificado
        $status_filter = '';
        $params = [':tecnico_id' => $tecnico['id']];
        
        if (isset($_GET['status']) && in_array($_GET['status'], ['pendente', 'concluido', 'cancelado'])) {
            $status_filter = "AND a.status = :status";
            $params[':status'] = $_GET['status'];
        }
        
        $query = "SELECT a.*, 
                 IFNULL((SELECT nome FROM clientes WHERE id = a.cliente_id), 'Cliente não encontrado') as cliente_nome,
                 IFNULL((SELECT titulo FROM servicos WHERE id = a.servico_id), 'Serviço não encontrado') as servico_nome
                 FROM agendamentos a 
                 WHERE a.tecnico_id = :tecnico_id $status_filter
                 ORDER BY a.data_agendamento DESC";
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Ignorar erro
    }
}

// Título da página
$page_title = "Meus Agendamentos";
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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
           <a href="tecnico-agendamentos.php" class="sidebar-menu-item active">
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
           <a href="admin-login.php?logout=1" class="sidebar-menu-item">
               <i class="fas fa-sign-out-alt"></i>
               Sair
           </a>
       </div>
   </aside>
   
   <!-- Main Content -->
   <main class="admin-content">
       <div class="page-header">
           <h1 class="page-title">Meus Agendamentos</h1>
           
           <nav aria-label="breadcrumb">
               <ol class="breadcrumb">
                   <li class="breadcrumb-item"><a href="tecnico-dashboard.php">Dashboard</a></li>
                   <li class="breadcrumb-item active" aria-current="page">Agendamentos</li>
               </ol>
           </nav>
       </div>
       
       <!-- Filtros -->
       <div class="row mb-4">
           <div class="col-md-12">
               <div class="card shadow-sm">
                   <div class="card-body">
                       <div class="d-flex flex-wrap">
                           <a href="tecnico-agendamentos.php" class="btn <?= !isset($_GET['status']) ? 'btn-primary' : 'btn-outline-primary' ?> mr-2 mb-2">
                               Todos
                           </a>
                           <a href="tecnico-agendamentos.php?status=pendente" class="btn <?= isset($_GET['status']) && $_GET['status'] === 'pendente' ? 'btn-warning' : 'btn-outline-warning' ?> mr-2 mb-2">
                               Pendentes
                           </a>
                           <a href="tecnico-agendamentos.php?status=concluido" class="btn <?= isset($_GET['status']) && $_GET['status'] === 'concluido' ? 'btn-success' : 'btn-outline-success' ?> mr-2 mb-2">
                               Concluídos
                           </a>
                           <a href="tecnico-agendamentos.php?status=cancelado" class="btn <?= isset($_GET['status']) && $_GET['status'] === 'cancelado' ? 'btn-danger' : 'btn-outline-danger' ?> mb-2">
                               Cancelados
                           </a>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <!-- Tabela de Agendamentos -->
       <div class="data-table-card">
           <div class="data-table-header">
               <h2 class="data-table-title">
                   <i class="fas fa-calendar-check"></i>
                   Lista de Agendamentos
               </h2>
               <a href="tecnico-calendario.php" class="btn btn-sm btn-outline-primary">
                   <i class="fas fa-calendar-alt"></i> Ver Calendário
               </a>
           </div>
           <div class="data-table-body">
               <div class="data-table-responsive">
                   <table class="data-table table" id="agendamentosTable">
                       <thead>
                           <tr>
                               <th>ID</th>
                               <th>Cliente</th>
                               <th>Serviço</th>
                               <th>Data</th>
                               <th>Horário</th>
                               <th>Status</th>
                               <th class="text-center">Ações</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php if (empty($agendamentos)): ?>
                           <tr>
                               <td colspan="7" class="text-center">Nenhum agendamento encontrado.</td>
                           </tr>
                           <?php else: ?>
                               <?php foreach ($agendamentos as $agendamento): ?>
                               <tr>
                                   <td><?= $agendamento['id'] ?></td>
                                   <td><?= htmlspecialchars($agendamento['cliente_nome']) ?></td>
                                   <td><?= htmlspecialchars($agendamento['servico_nome']) ?></td>
                                   <td><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></td>
                                   <td><?= date('H:i', strtotime($agendamento['hora_inicio'])) ?> - <?= isset($agendamento['hora_fim']) ? date('H:i', strtotime($agendamento['hora_fim'])) : '?' ?></td>
                                   <td>
                                       <?php
                                       $status_class = 'badge-info';
                                       $status_text = 'Em andamento';
                                       
                                       if (isset($agendamento['status'])) {
                                           switch ($agendamento['status']) {
                                               case 'pendente':
                                                   $status_class = 'badge-warning';
                                                   $status_text = 'Pendente';
                                                   break;
                                               case 'concluido':
                                                   $status_class = 'badge-success';
                                                   $status_text = 'Concluído';
                                                   break;
                                               case 'cancelado':
                                                   $status_class = 'badge-danger';
                                                   $status_text = 'Cancelado';
                                                   break;
                                           }
                                       }
                                       ?>
                                       <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                   </td>
                                   <td class="text-center">
                                       <div class="btn-group">
                                           <a href="tecnico-agendamento.php?id=<?= $agendamento['id'] ?>" class="btn btn-sm btn-info" title="Visualizar">
                                               <i class="fas fa-eye"></i>
                                           </a>
                                           <?php if (isset($agendamento['status']) && $agendamento['status'] === 'pendente'): ?>
                                           <button type="button" class="btn btn-sm btn-success update-status" data-id="<?= $agendamento['id'] ?>" data-status="concluido" title="Marcar como Concluído">
                                               <i class="fas fa-check"></i>
                                           </button>
                                           <button type="button" class="btn btn-sm btn-danger update-status" data-id="<?= $agendamento['id'] ?>" data-status="cancelado" title="Cancelar">
                                               <i class="fas fa-times"></i>
                                           </button>
                                           <?php endif; ?>
                                       </div>
                                   </td>
                               </tr>
                               <?php endforeach; ?>
                           <?php endif; ?>
                       </tbody>
                   </table>
               </div>
           </div>
       </div>
   </main>
   
   <!-- Modal de Atualização de Status -->
   <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="statusModalLabel">Atualizar Status</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   <p>Tem certeza que deseja atualizar o status deste agendamento?</p>
                   <form id="statusForm" method="post" action="tecnico-atualizar-status.php">
                       <input type="hidden" name="agendamento_id" id="agendamento_id">
                       <input type="hidden" name="status" id="status">
                       <div class="form-group">
                           <label for="observacoes">Observações:</label>
                           <textarea class="form-control" name="observacoes" id="observacoes" rows="3"></textarea>
                       </div>
                   </form>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                   <button type="button" class="btn btn-primary" id="confirmStatus">Confirmar</button>
               </div>
           </div>
       </div>
   </div>
   
   <!-- JavaScript -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
   <script>
       $(document).ready(function() {
           // Inicializar DataTable
           $('#agendamentosTable').DataTable({
               language: {
                   url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
               },
               responsive: true,
               pageLength: 10,
               order: [[3, 'desc'], [4, 'asc']]
           });
           
           // Toggle sidebar on mobile
           $('#sidebarToggle').on('click', function() {
               $('#sidebar').toggleClass('show');
           });
           
           // Toggle user dropdown
           $('#userDropdown').on('click', function(e) {
               e.stopPropagation();
               $('#userDropdownMenu').toggleClass('show');
           });
           
           // Close dropdown when clicking outside
           $(document).on('click', function(e) {
               if (!$(e.target).closest('.user-dropdown').length) {
                   $('#userDropdownMenu').removeClass('show');
               }
           });
           
           // Atualizar status
           $('.update-status').on('click', function() {
               const id = $(this).data('id');
               const status = $(this).data('status');
               
               $('#agendamento_id').val(id);
               $('#status').val(status);
               
               // Atualizar título do modal
               let title = 'Atualizar Status';
               if (status === 'concluido') {
                   title = 'Marcar como Concluído';
               } else if (status === 'cancelado') {
                   title = 'Cancelar Agendamento';
               }
               $('#statusModalLabel').text(title);
               
               $('#statusModal').modal('show');
           });
           
           // Confirmar atualização de status
           $('#confirmStatus').on('click', function() {
               $('#statusForm').submit();
           });
       });
   </script>
</body>
</html>