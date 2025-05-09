<!DOCTYPE html>
<html lang="pt-br">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Simão Refrigeração - Painel Administrativo</title>
   
   <!-- Favicon -->
   <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
   
   <!-- Fontes -->
   <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
   
   <!-- CSS -->
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
   <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css">
   <link rel="stylesheet" href="assets/css/admin.css">
   
   <!-- JavaScript -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/pt-br.js"></script>
</head>
<body>
   <div class="admin-wrapper">
       <!-- Sidebar -->
       <?php include 'views/admin/includes/sidebar.php'; ?>
       
       <!-- Conteúdo Principal -->
       <div class="main-content">
           <!-- Topbar -->
           <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
               <!-- Botão para recolher sidebar -->
               <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                   <i class="fa fa-bars"></i>
               </button>
               
               <!-- Ver Site -->
               <a href="./" target="_blank" class="btn btn-sm btn-outline-primary mr-3">
                   <i class="fas fa-external-link-alt mr-1"></i>
                   Ver Site
               </a>
               
               <!-- Topbar Navbar -->
               <ul class="navbar-nav ml-auto">
                   <!-- Notificações -->
                   <li class="nav-item dropdown no-arrow mx-1">
                       <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="fas fa-bell fa-fw"></i>
                           <?php
                           // Verificar se há notificações
                           $db = db_connect();
                           $query = "SELECT COUNT(*) as total FROM contatos WHERE status = 'novo'";
                           $stmt = $db->prepare($query);
                           $stmt->execute();
                           $result = $stmt->fetch(PDO::FETCH_ASSOC);
                           $notificacoes = $result['total'] ?? 0;
                           
                           if ($notificacoes > 0):
                           ?>
                           <span class="badge badge-danger badge-counter"><?= $notificacoes ?></span>
                           <?php endif; ?>
                       </a>
                       <!-- Dropdown - Notificações -->
                       <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                           <h6 class="dropdown-header">
                               Notificações
                           </h6>
                           <?php
                           if ($notificacoes > 0) {
                               $query = "SELECT * FROM contatos WHERE status = 'novo' ORDER BY data_criacao DESC LIMIT 5";
                               $stmt = $db->prepare($query);
                               $stmt->execute();
                               $contatos_novos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                               
                               foreach ($contatos_novos as $contato) {
                                   echo '<a class="dropdown-item d-flex align-items-center" href="admin-contatos.php?action=view&id=' . $contato['id'] . '">';
                                   echo '<div class="mr-3">';
                                   echo '<div class="icon-circle bg-primary">';
                                   echo '<i class="fas fa-envelope text-white"></i>';
                                   echo '</div>';
                                   echo '</div>';
                                   echo '<div>';
                                   echo '<div class="small text-gray-500">' . date('d/m/Y H:i', strtotime($contato['data_criacao'])) . '</div>';
                                   echo '<span class="font-weight-bold">' . htmlspecialchars($contato['nome']) . ' - ' . htmlspecialchars(substr($contato['mensagem'], 0, 50)) . (strlen($contato['mensagem']) > 50 ? '...' : '') . '</span>';
                                   echo '</div>';
                                   echo '</a>';
                               }
                               
                               echo '<a class="dropdown-item text-center small text-gray-500" href="admin-contatos.php?status=novo">Ver todas as notificações</a>';
                           } else {
                               echo '<div class="dropdown-item text-center small text-gray-500">Nenhuma notificação nova</div>';
                           }
                           ?>
                       </div>
                   </li>
                   
                   <div class="topbar-divider d-none d-sm-block"></div>
                   
                   <!-- Informações do Usuário -->
                   <li class="nav-item dropdown no-arrow">
                       <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                               <?= $_SESSION['usuario_nome'] ?? 'Administrador' ?>
                           </span>
                           <img class="img-profile rounded-circle" src="https://via.placeholder.com/60x60">
                       </a>
                       <!-- Dropdown - Informações do Usuário -->
                       <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                           <a class="dropdown-item" href="admin-profile.php">
                               <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                               Perfil
                           </a>
                           <a class="dropdown-item" href="admin-settings.php">
                               <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                               Configurações
                           </a>
                           <div class="dropdown-divider"></div>
                           <a class="dropdown-item" href="logout.php" data-toggle="modal" data-target="#logoutModal">
                               <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                               Sair
                           </a>
                       </div>
                   </li>
               </ul>
           </nav>
           
           <!-- Conteúdo da Página -->
           <div class="content">
