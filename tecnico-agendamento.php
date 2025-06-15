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

// Verificar se o ID do agendamento foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: tecnico-dashboard.php');
    exit;
}

$agendamento_id = (int)$_GET['id'];
$db = db_connect();
$agendamento = null;
$tecnico = null;

// Buscar informações do técnico
try {
    $query = "SELECT * FROM tecnicos WHERE usuario_id = :usuario_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt->execute();
    $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tecnico) {
        // Criar um array com valores padrão
        $tecnico = [
            'id' => 0,
            'nome' => $_SESSION['user_nome'] ?? 'Técnico',
            'email' => $_SESSION['user_email'] ?? '',
            'usuario_id' => $_SESSION['user_id'],
            'disponivel' => true
        ];
    }
} catch (Exception $e) {
    // Criar um array com valores padrão em caso de erro
    $tecnico = [
        'id' => 0,
        'nome' => $_SESSION['user_nome'] ?? 'Técnico',
        'email' => $_SESSION['user_email'] ?? '',
        'usuario_id' => $_SESSION['user_id'],
        'disponivel' => true
    ];
}

// Buscar informações do agendamento
try {
    $query = "SELECT a.*, 
             c.nome as cliente_nome, c.telefone as cliente_telefone, c.email as cliente_email, c.endereco as cliente_endereco,
             s.nome as servico_nome, s.descricao as servico_descricao, s.preco as servico_preco
             FROM agendamentos a 
             LEFT JOIN clientes c ON a.cliente_id = c.id
             LEFT JOIN servicos s ON a.servico_id = s.id
             WHERE a.id = :id
             LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $agendamento_id);
    $stmt->execute();
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        // Inicializar com valores vazios para evitar erros
        $agendamento = [
            'id' => $agendamento_id,
            'cliente_nome' => 'Cliente não encontrado',
            'cliente_telefone' => '',
            'cliente_email' => '',
            'cliente_endereco' => '',
            'servico_nome' => 'Serviço não encontrado',
            'servico_descricao' => '',
            'servico_preco' => 0,
            'data_agendamento' => date('Y-m-d H:i:s'),
            'status' => 'pendente'
        ];
    }
} catch (Exception $e) {
    // Inicializar com valores vazios para evitar erros
    $agendamento = [
        'id' => $agendamento_id,
        'cliente_nome' => 'Cliente não encontrado',
        'cliente_telefone' => '',
        'cliente_email' => '',
        'cliente_endereco' => '',
        'servico_nome' => 'Serviço não encontrado',
        'servico_descricao' => '',
        'servico_preco' => 0,
        'data_agendamento' => date('Y-m-d H:i:s'),
        'status' => 'pendente'
    ];
}

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $novo_status = $_POST['status'];
        $observacao = isset($_POST['observacao']) ? $_POST['observacao'] : '';
        
        try {
            // Verificar a estrutura da tabela para debug
            error_log("Atualizando status para: " . $novo_status);
            
            // Verificar se o agendamento existe
            $check_query = "SELECT id FROM agendamentos WHERE id = :id LIMIT 1";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(':id', $agendamento_id);
            $check_stmt->execute();
            
            // Converter o status para um caractere único
            $status_char = 'p'; // padrão: pendente
            
            switch($novo_status) {
                case 'p': 
                case 'pendente': 
                    $status_char = 'p'; 
                    break;
                case 'a': 
                case 'em_andamento': 
                    $status_char = 'a'; 
                    break;
                case 'c': 
                case 'concluido': 
                    $status_char = 'c'; 
                    break;
                case 'x': 
                case 'cancelado': 
                    $status_char = 'x'; 
                    break;
            }
            
            if ($check_stmt->rowCount() > 0) {
                // Agendamento existe, atualizar apenas o status
                $query = "UPDATE agendamentos SET status = :status, data_atualizacao = NOW() WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':status', $status_char);
                $stmt->bindParam(':id', $agendamento_id);
                $stmt->execute();
            } else {
                // Agendamento não existe, criar
                $query = "INSERT INTO agendamentos (id, tecnico_id, cliente_id, servico_id, data_agendamento, status, data_criacao, data_atualizacao) 
                          VALUES (:id, :tecnico_id, 1, 1, NOW(), :status, NOW(), NOW())";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $agendamento_id);
                $stmt->bindParam(':tecnico_id', $tecnico['id']);
                $stmt->bindParam(':status', $status_char);
                $stmt->execute();
            }
            
            // Atualizar o array de agendamento com os novos valores
            $agendamento['status'] = $status_char;
            
            // Definir mensagem de sucesso
            $success = "Agendamento atualizado com sucesso!";
        } catch (Exception $e) {
            $error = "Erro ao atualizar o status do agendamento: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Agendamento | <?= defined('SITE_NAME') ? SITE_NAME : 'Simão Refrigeração' ?></title>
    
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
        
        /* Main Content */
        .admin-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 2rem;
            flex: 1;
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
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: var(--white);
            border-bottom: 1px solid #e5e7eb;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }
        
        .card-body {
            padding: 1.5rem;
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
        }
    </style>
</head>
<body>
   <!-- Header -->
   <header class="admin-header">
       <div class="d-flex align-items-center">
           <button class="btn btn-link p-0 mr-3 d-lg-none" id="sidebarToggle">
               <i class="fas fa-bars"></i>
           </button>
           <a href="tecnico-dashboard.php" class="header-brand">
               <i class="fas fa-snowflake"></i>
               <span>Simão Refrigeração</span>
           </a>
       </div>
       
       <div class="header-actions">
           <div class="user-dropdown">
               <div class="user-dropdown-toggle" id="userDropdown">
                   <i class="fas fa-user-circle fa-2x text-primary"></i>
                   <div class="d-none d-md-block ml-2">
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
           <a href="logout.php" class="sidebar-menu-item" id="sidebarLogoutLink">
               <i class="fas fa-sign-out-alt"></i>
               Sair
           </a>
       </div>
   </aside>
   
   <!-- Main Content -->
   <main class="admin-content">
       <div class="page-header">
           <h1 class="page-title">Detalhes do Agendamento</h1>
           
           <nav aria-label="breadcrumb">
               <ol class="breadcrumb bg-transparent p-0 m-0">
                   <li class="breadcrumb-item"><a href="tecnico-dashboard.php">Dashboard</a></li>
                   <li class="breadcrumb-item"><a href="tecnico-agendamentos.php">Agendamentos</a></li>
                   <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
               </ol>
           </nav>
       </div>
       
       <?php if (isset($success)): ?>
       <div class="alert alert-success alert-dismissible fade show" role="alert">
           <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
               <span aria-hidden="true">&times;</span>
           </button>
       </div>
       <?php elseif (isset($_GET['success'])): ?>
       <div class="alert alert-success alert-dismissible fade show" role="alert">
           <i class="fas fa-check-circle mr-2"></i> Agendamento atualizado com sucesso!
           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
               <span aria-hidden="true">&times;</span>
           </button>
       </div>
       <?php endif; ?>
       
       <?php if (isset($error)): ?>
       <div class="alert alert-danger alert-dismissible fade show" role="alert">
           <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
           <button type="button" class="close" data-dismiss="alert" aria-label="Close">
               <span aria-hidden="true">&times;</span>
           </button>
       </div>
       <?php endif; ?>
       
       <div class="row">
           <div class="col-lg-8">
               <!-- Detalhes do Agendamento -->
               <div class="card">
                   <div class="card-header">
                       <h2 class="card-title">
                           <i class="fas fa-calendar-alt text-primary mr-2"></i>
                           Informações do Agendamento
                       </h2>
                       
                       <?php
                       $status_class = 'badge-info';
                       $status_text = 'Em andamento';
                       
                       if (isset($agendamento['status'])) {
                           switch ($agendamento['status']) {
                               case 'pendente':
                               case 'p':
                                   $status_class = 'badge-warning';
                                   $status_text = 'Pendente';
                                   break;
                               case 'em_andamento':
                               case 'a':
                                   $status_class = 'badge-info';
                                   $status_text = 'Em andamento';
                                   break;
                               case 'concluido':
                               case 'c':
                                   $status_class = 'badge-success';
                                   $status_text = 'Concluído';
                                   break;
                               case 'cancelado':
                               case 'x':
                                   $status_class = 'badge-danger';
                                   $status_text = 'Cancelado';
                                   break;
                               default:
                                   $status_class = 'badge-secondary';
                                   $status_text = 'Status desconhecido';
                           }
                       }
                       ?>
                       
                       <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                   </div>
                   <div class="card-body">
                       <div class="row">
                           <div class="col-md-6">
                               <p><strong>Data:</strong> <?= isset($agendamento['data_agendamento']) ? format_date($agendamento['data_agendamento'], 'd/m/Y') : 'N/A' ?></p>
                               <p><strong>Horário:</strong> <?= isset($agendamento['data_agendamento']) ? format_date($agendamento['data_agendamento'], 'H:i') : 'N/A' ?></p>
                               <p><strong>Serviço:</strong> <?= isset($agendamento['servico_nome']) ? htmlspecialchars($agendamento['servico_nome']) : 'N/A' ?></p>
                               <p><strong>Preço:</strong> R$ <?= isset($agendamento['servico_preco']) ? number_format((float)$agendamento['servico_preco'], 2, ',', '.') : '0,00' ?></p>
                           </div>
                           <div class="col-md-6">
                               <p><strong>Cliente:</strong> <?= isset($agendamento['cliente_nome']) ? htmlspecialchars($agendamento['cliente_nome']) : 'N/A' ?></p>
                               <p><strong>Telefone:</strong> <?= isset($agendamento['cliente_telefone']) ? htmlspecialchars($agendamento['cliente_telefone']) : 'N/A' ?></p>
                               <p><strong>Email:</strong> <?= isset($agendamento['cliente_email']) ? htmlspecialchars($agendamento['cliente_email']) : 'N/A' ?></p>
                               <p><strong>Endereço:</strong> <?= isset($agendamento['cliente_endereco']) ? htmlspecialchars($agendamento['cliente_endereco']) : 'N/A' ?></p>
                           </div>
                       </div>
                       
                       <?php if (!empty($agendamento['servico_descricao'])): ?>
                       <div class="mt-4">
                           <h5>Descrição do Serviço</h5>
                           <p><?= nl2br(htmlspecialchars($agendamento['servico_descricao'])) ?></p>
                       </div>
                       <?php endif; ?>
                       

                   </div>
               </div>
           </div>
           
           <div class="col-lg-4">
               <!-- Atualizar Status -->
               <div class="card">
                   <div class="card-header">
                       <h2 class="card-title">
                           <i class="fas fa-edit text-primary mr-2"></i>
                           Atualizar Status
                       </h2>
                   </div>
                   <div class="card-body">
                       <form method="POST" action="">
                           <input type="hidden" name="action" value="update_status">
                           
                           <div class="form-group">
                               <label for="status">Status</label>
                               <select class="form-control" id="status" name="status" required>
                                   <option value="p" <?= isset($agendamento['status']) && $agendamento['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                   <option value="a" <?= isset($agendamento['status']) && $agendamento['status'] === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                   <option value="c" <?= isset($agendamento['status']) && $agendamento['status'] === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                   <option value="x" <?= isset($agendamento['status']) && $agendamento['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                               </select>
                           </div>
                           
                           <div class="form-group">
                               <label for="observacao">Observações</label>
                               <textarea class="form-control" id="observacao" name="observacao" rows="4"></textarea>
                               <small class="form-text text-muted">Adicione observações sobre o atendimento</small>
                           </div>
                           
                           <button type="submit" class="btn btn-primary btn-block">
                               <i class="fas fa-save mr-2"></i> Salvar Alterações
                           </button>
                       </form>
                   </div>
               </div>
               
               <!-- Ações Rápidas -->
               <div class="card">
                   <div class="card-header">
                       <h2 class="card-title">
                           <i class="fas fa-bolt text-primary mr-2"></i>
                           Ações Rápidas
                       </h2>
                   </div>
                   <div class="card-body">
                       <a href="tecnico-agendamentos.php" class="btn btn-outline-secondary btn-block mb-2">
                           <i class="fas fa-arrow-left mr-2"></i> Voltar para Agendamentos
                       </a>
                       
                       <a href="tecnico-dashboard.php" class="btn btn-outline-primary btn-block">
                           <i class="fas fa-tachometer-alt mr-2"></i> Ir para Dashboard
                       </a>
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
       
       // User dropdown
       document.addEventListener('DOMContentLoaded', function() {
           const userDropdown = document.getElementById('userDropdown');
           const userDropdownMenu = document.getElementById('userDropdownMenu');
           
           userDropdown.addEventListener('click', function(e) {
               e.preventDefault();
               e.stopPropagation();
               userDropdownMenu.classList.toggle('show');
           });
           
           // Close dropdown when clicking outside
           document.addEventListener('click', function(e) {
               if (!e.target.closest('.user-dropdown')) {
                   userDropdownMenu.classList.remove('show');
               }
           });
           
           // Logout links
           const logoutLink = document.getElementById('logoutLink');
           const sidebarLogoutLink = document.getElementById('sidebarLogoutLink');
           
           logoutLink.addEventListener('click', function(e) {
               e.preventDefault();
               window.location.href = 'logout.php';
           });
           
           sidebarLogoutLink.addEventListener('click', function(e) {
               e.preventDefault();
               window.location.href = 'logout.php';
           });
       });
   </script>
</body>
</html>