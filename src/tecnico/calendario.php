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

// Título da página
$page_title = "Calendário de Agendamentos";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
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
        
        /* Calendar */
        .calendar-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }
        
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .fc .fc-button-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .fc .fc-button-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .fc .fc-button-primary:disabled {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
        }
        
        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .fc-event {
            cursor: pointer;
        }
        
        .fc-event.pendente {
            background-color: var(--warning);
            border-color: var(--warning);
        }
        
        .fc-event.concluido {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .fc-event.cancelado {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        /* Modal */
        .modal-header {
            background-color: var(--primary);
            color: var(--white);
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .modal-header .close {
            color: var(--white);
            opacity: 0.8;
        }
        
        .modal-header .close:hover {
            opacity: 1;
        }
        
        .modal-footer {
            border-top: 1px solid #e5e7eb;
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
            
            .fc .fc-toolbar.fc-header-toolbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .fc .fc-toolbar-title {
                font-size: 1.125rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .admin-content {
                padding: 1rem;
            }
            
            .calendar-card {
                padding: 1rem;
            }
            
            .fc .fc-toolbar.fc-header-toolbar {
                margin-bottom: 1rem;
            }
            
            .fc .fc-toolbar-title {
                font-size: 1rem;
            }
            
            .fc .fc-button {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
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
           <a href="tecnico-calendario.php" class="sidebar-menu-item active">
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
           <h1 class="page-title">Calendário de Agendamentos</h1>
           
           <nav aria-label="breadcrumb">
               <ol class="breadcrumb">
                   <li class="breadcrumb-item"><a href="tecnico-dashboard.php">Dashboard</a></li>
                   <li class="breadcrumb-item active" aria-current="page">Calendário</li>
               </ol>
           </nav>
       </div>
       
       <!-- Calendário -->
       <div class="calendar-card">
           <div id="calendar"></div>
       </div>
   </main>
   
   <!-- Modal de Detalhes do Agendamento -->
   <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="eventModalLabel">Detalhes do Agendamento</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   <div class="event-details">
                       <div class="form-group">
                           <label>Título:</label>
                           <p id="event-title" class="font-weight-bold"></p>
                       </div>
                       <div class="form-group">
                           <label>Cliente:</label>
                           <p id="event-client"></p>
                       </div>
                       <div class="form-group">
                           <label>Serviço:</label>
                           <p id="event-service"></p>
                       </div>
                       <div class="form-group">
                           <label>Data e Hora:</label>
                           <p id="event-datetime"></p>
                       </div>
                       <div class="form-group">
                           <label>Status:</label>
                           <p id="event-status"></p>
                       </div>
                       <div class="form-group">
                           <label>Observações:</label>
                           <p id="event-notes"></p>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                   <a href="#" class="btn btn-primary" id="view-event">Ver Detalhes</a>
                   <button type="button" class="btn btn-success" id="complete-event">Marcar como Concluído</button>
                   <button type="button" class="btn btn-danger" id="cancel-event">Cancelar Agendamento</button>
               </div>
           </div>
       </div>
   </div>
   
   <!-- JavaScript -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           // Inicializar FullCalendar
           var calendarEl = document.getElementById('calendar');
           var calendar = new FullCalendar.Calendar(calendarEl, {
               initialView: 'dayGridMonth',
               headerToolbar: {
                   left: 'prev,next today',
                   center: 'title',
                   right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
               },
               locale: 'pt-br',
               buttonText: {
                   today: 'Hoje',
                   month: 'Mês',
                   week: 'Semana',
                   day: 'Dia',
                   list: 'Lista'
               },
               themeSystem: 'bootstrap',
               height: 'auto',
               events: function(info, successCallback, failureCallback) {
                   // Buscar eventos via AJAX
                   $.ajax({
                       url: 'tecnico-api.php',
                       type: 'GET',
                       data: {
                           action: 'get_events',
                           tecnico_id: <?= $tecnico ? $tecnico['id'] : 'null' ?>,
                           start: info.startStr,
                           end: info.endStr
                       },
                       success: function(response) {
                           var events = [];
                           
                           if (response && response.success && response.data) {
                               response.data.forEach(function(event) {
                                   var backgroundColor = '#3b82f6'; // default blue
                                   
                                   if (event.status === 'pendente') {
                                       backgroundColor = '#f59e0b'; // warning
                                   } else if (event.status === 'concluido') {
                                       backgroundColor = '#10b981'; // success
                                   } else if (event.status === 'cancelado') {
                                       backgroundColor = '#ef4444'; // danger
                                   }
                                   
                                   events.push({
                                       id: event.id,
                                       title: event.titulo,
                                       start: event.data_agendamento + 'T' + event.hora_inicio,
                                       end: event.hora_fim ? event.data_agendamento + 'T' + event.hora_fim : null,
                                       backgroundColor: backgroundColor,
                                       borderColor: backgroundColor,
                                       classNames: [event.status],
                                       extendedProps: {
                                           cliente: event.cliente_nome,
                                           servico: event.servico_nome,
                                           status: event.status,
                                           observacoes: event.observacoes
                                       }
                                   });
                               });
                           }
                           
                           successCallback(events);
                       },
                       error: function() {
                           failureCallback();
                       }
                   });
               },
               eventClick: function(info) {
                   // Mostrar detalhes do evento no modal
                   $('#event-title').text(info.event.title);
                   $('#event-client').text(info.event.extendedProps.cliente);
                   $('#event-service').text(info.event.extendedProps.servico);
                   
                   var start = new Date(info.event.start);
                   var end = info.event.end ? new Date(info.event.end) : null;
                   var dateStr = start.toLocaleDateString('pt-BR');
                   var timeStr = start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                   
                   if (end) {
                       timeStr += ' - ' + end.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                   }
                   
                   $('#event-datetime').text(dateStr + ' ' + timeStr);
                   
                   var statusText = 'Pendente';
                   var statusClass = 'badge badge-warning';
                   
                   if (info.event.extendedProps.status === 'concluido') {
                       statusText = 'Concluído';
                       statusClass = 'badge badge-success';
                   } else if (info.event.extendedProps.status === 'cancelado') {
                       statusText = 'Cancelado';
                       statusClass = 'badge badge-danger';
                   }
                   
                   $('#event-status').html('<span class="' + statusClass + '">' + statusText + '</span>');
                   $('#event-notes').text(info.event.extendedProps.observacoes || 'Nenhuma observação');
                   
                   // Configurar botões de ação
                   $('#view-event').attr('href', 'tecnico-agendamento.php?id=' + info.event.id);
                   
                   // Mostrar/ocultar botões de ação com base no status
                   if (info.event.extendedProps.status === 'pendente') {
                       $('#complete-event, #cancel-event').show();
                   } else {
                       $('#complete-event, #cancel-event').hide();
                   }
                   
                   // Configurar ações dos botões
                   $('#complete-event').off('click').on('click', function() {
                       updateEventStatus(info.event.id, 'concluido');
                   });
                   
                   $('#cancel-event').off('click').on('click', function() {
                       updateEventStatus(info.event.id, 'cancelado');
                   });
                   
                   $('#eventModal').modal('show');
               }
           });
           
           calendar.render();
           
           // Função para atualizar status do evento
           function updateEventStatus(eventId, status) {
               $.ajax({
                   url: 'tecnico-api.php',
                   type: 'POST',
                   data: {
                       action: 'update_status',
                       agendamento_id: eventId,
                       status: status
                   },
                   success: function(response) {
                       if (response && response.success) {
                           $('#eventModal').modal('hide');
                           calendar.refetchEvents();
                       } else {
                           alert('Erro ao atualizar status: ' + (response.message || 'Erro desconhecido'));
                       }
                   },
                   error: function() {
                       alert('Erro ao processar a solicitação');
                   }
               });
           }
           
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
       });
   </script>
</body>
</html>