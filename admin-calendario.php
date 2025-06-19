<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin-login.php');
}

// Conectar ao banco de dados
$db = db_connect();

// Obter todos os técnicos para o filtro
try {
    $stmt = $db->prepare("SELECT id, nome FROM tecnicos ORDER BY nome");
    $stmt->execute();
    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tecnicos = [];
}

// Obter todos os serviços para o filtro
try {
    $stmt = $db->prepare("SELECT id, titulo FROM servicos ORDER BY titulo");
    $stmt->execute();
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $servicos = [];
}

// Obter agendamentos
$agendamentos = [];
try {
    // Filtrar por status se especificado
    $status_filter = '';
    $params = [];
    
    if (isset($_GET['status']) && in_array($_GET['status'], ['pendente', 'concluido', 'cancelado'])) {
        $status_filter = "WHERE a.status = :status";
        $params[':status'] = $_GET['status'];
    }
    
    $query = "SELECT a.*, 
             IFNULL((SELECT nome FROM clientes WHERE id = a.cliente_id), 'Cliente não encontrado') as cliente_nome,
             IFNULL((SELECT titulo FROM servicos WHERE id = a.servico_id), 'Serviço não encontrado') as servico_nome,
             IFNULL((SELECT nome FROM tecnicos WHERE id = a.tecnico_id), 'Técnico não encontrado') as tecnico_nome
             FROM agendamentos a 
             $status_filter
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
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
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
        
        .header-brand i {
            color: var(--primary);
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }
        
        .admin-sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 250px;
            background-color: var(--dark);
            color: var(--white);
            overflow-y: auto;
            z-index: 1020;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
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
        
        .data-table-body {
            padding: 1.5rem;
        }
        
        #calendar {
            height: 600px;
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
    </style>
</head>
<body>
   <!-- Header -->
   <header class="admin-header">
       <a href="admin-dashboard.php" class="header-brand">
           <i class="fas fa-snowflake"></i>
           Simão Refrigeração
       </a>
       
       <div class="header-actions">
           <a href="index.php" target="_blank" class="btn btn-outline-secondary">
               <i class="fas fa-external-link-alt"></i>
               Ver Site
           </a>
           
           <div class="dropdown">
               <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                   <i class="fas fa-user"></i>
                   <?= $_SESSION['user_nome'] ?>
               </button>
               <div class="dropdown-menu dropdown-menu-right">
                   <a class="dropdown-item" href="admin-profile.php">
                       <i class="fas fa-user"></i> Perfil
                   </a>
                   <div class="dropdown-divider"></div>
                   <a class="dropdown-item" href="admin-login.php?logout=1">
                       <i class="fas fa-sign-out-alt"></i> Sair
                   </a>
               </div>
           </div>
       </div>
   </header>
   
   <!-- Sidebar -->
   <aside class="admin-sidebar">
       <div class="sidebar-menu">
           <a href="admin-dashboard.php" class="sidebar-menu-item">
               <i class="fas fa-tachometer-alt"></i>
               Dashboard
           </a>
           <a href="admin-agendamentos.php" class="sidebar-menu-item">
               <i class="fas fa-calendar-check"></i>
               Agendamentos
           </a>
           <a href="admin-calendario.php" class="sidebar-menu-item active">
               <i class="fas fa-calendar-alt"></i>
               Calendário
           </a>
           <a href="admin-clientes.php" class="sidebar-menu-item">
               <i class="fas fa-users"></i>
               Clientes
           </a>
           <a href="admin-tecnicos.php" class="sidebar-menu-item">
               <i class="fas fa-user-hard-hat"></i>
               Técnicos
           </a>
           <a href="admin-servicos.php" class="sidebar-menu-item">
               <i class="fas fa-tools"></i>
               Serviços
           </a>
       </div>
   </aside>
   
   <!-- Main Content -->
   <main class="admin-content">
       <div class="page-header">
           <h1 class="page-title">
               <i class="fas fa-calendar-alt mr-2"></i>
               Calendário de Agendamentos
           </h1>
           
           <div class="btn-group">
               <a href="admin-agendamentos.php" class="btn btn-outline-primary">
                   <i class="fas fa-list"></i> Lista
               </a>
               <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                   <i class="fas fa-filter"></i> Filtros
               </button>
               <div class="dropdown-menu dropdown-menu-right p-3" style="width: 300px;">
                   <form id="filterForm">
                       <div class="form-group">
                           <label for="tecnico_id">Técnico</label>
                           <select class="form-control form-control-sm" id="tecnico_id" name="tecnico_id">
                               <option value="">Todos os técnicos</option>
                               <?php foreach ($tecnicos as $tecnico): ?>
                               <option value="<?= $tecnico['id'] ?>"><?= $tecnico['nome'] ?></option>
                               <?php endforeach; ?>
                           </select>
                       </div>
                       <div class="form-group">
                           <label for="servico_id">Serviço</label>
                           <select class="form-control form-control-sm" id="servico_id" name="servico_id">
                               <option value="">Todos os serviços</option>
                               <?php foreach ($servicos as $servico): ?>
                               <option value="<?= $servico['id'] ?>"><?= $servico['titulo'] ?></option>
                               <?php endforeach; ?>
                           </select>
                       </div>
                       <div class="form-group">
                           <label for="status">Status</label>
                           <select class="form-control form-control-sm" id="status" name="status">
                               <option value="">Todos os status</option>
                               <option value="pendente">Pendente</option>
                               <option value="concluido">Concluído</option>
                               <option value="cancelado">Cancelado</option>
                           </select>
                       </div>
                       <div class="form-group mb-0">
                           <button type="submit" class="btn btn-primary btn-sm btn-block">Aplicar Filtros</button>
                           <button type="button" id="resetFilters" class="btn btn-secondary btn-sm btn-block mt-2">Limpar Filtros</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
       
       <!-- Calendário -->
       <div class="data-table-card">
           <div class="data-table-header">
               <h2 class="data-table-title">
                   <i class="fas fa-calendar-alt"></i>
                   Calendário de Agendamentos
               </h2>
           </div>
           <div class="data-table-body">
               <div id="calendar"></div>
           </div>
       </div>
   </main>

<!-- Modal Novo Agendamento -->
<div class="modal fade" id="novoAgendamentoModal" tabindex="-1" role="dialog" aria-labelledby="novoAgendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="novoAgendamentoModalLabel">Novo Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="admin-save.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="form" value="agendamento">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="titulo">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="pendente">Pendente</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="concluido">Concluído</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="cliente_id">Cliente</label>
                            <select class="form-control" id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione um cliente</option>
                                <?php
                                $stmt = $db->query("SELECT id, nome FROM clientes ORDER BY nome");
                                while ($cliente = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$cliente['id']}'>{$cliente['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="servico_id">Serviço</label>
                            <select class="form-control" id="servico_id" name="servico_id" required>
                                <option value="">Selecione um serviço</option>
                                <?php
                                $stmt = $db->query("SELECT id, titulo FROM servicos ORDER BY titulo");
                                while ($servico = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$servico['id']}'>{$servico['titulo']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="tecnico_id">Técnico Principal</label>
                            <select class="form-control" id="tecnico_id" name="tecnico_id" required>
                                <option value="">Selecione um técnico</option>
                                <?php
                                foreach ($tecnicos as $tecnico) {
                                    echo "<option value='{$tecnico['id']}'>{$tecnico['nome']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="data_agendamento">Data</label>
                            <input type="date" class="form-control" id="data_agendamento" name="data_agendamento" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="hora_inicio">Hora Início</label>
                            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="hora_fim">Hora Fim</label>
                            <input type="time" class="form-control" id="hora_fim" name="hora_fim">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalhes do Agendamento -->
<div class="modal fade" id="detalhesAgendamentoModal" tabindex="-1" role="dialog" aria-labelledby="detalhesAgendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhesAgendamentoModalLabel">Detalhes do Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Carregando...</span>
                    </div>
                </div>
                <div id="detalhesAgendamentoContent" style="display: none;">
                    <h4 id="agendamento_titulo" class="mb-3"></h4>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> <span id="agendamento_cliente"></span></p>
                            <p><strong>Serviço:</strong> <span id="agendamento_servico"></span></p>
                            <p><strong>Técnico:</strong> <span id="agendamento_tecnico"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Data:</strong> <span id="agendamento_data"></span></p>
                            <p><strong>Horário:</strong> <span id="agendamento_horario"></span></p>
                            <p><strong>Status:</strong> <span id="agendamento_status"></span></p>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Observações</h6>
                        </div>
                        <div class="card-body">
                            <p id="agendamento_observacoes">Nenhuma observação registrada.</p>
                        </div>
                    </div>
                    
                    <div id="agendamento_acoes" class="text-right">
                        <a href="#" id="btn_editar_agendamento" class="btn btn-info">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="#" id="btn_finalizar_agendamento" class="btn btn-success" data-toggle="modal" data-target="#finalizarAgendamentoModal">
                            <i class="fas fa-check"></i> Finalizar
                        </a>
                        <a href="#" id="btn_cancelar_agendamento" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Finalizar Agendamento -->
<div class="modal fade" id="finalizarAgendamentoModal" tabindex="-1" role="dialog" aria-labelledby="finalizarAgendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finalizarAgendamentoModalLabel">Finalizar Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="admin-save.php" method="post" id="formFinalizarAgendamento">
                <div class="modal-body">
                    <input type="hidden" name="form" value="finalizar_agendamento">
                    <input type="hidden" name="agendamento_id" id="finalizar_agendamento_id">
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="valor_cobrado">Valor Cobrado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_cobrado" name="valor_cobrado" required>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="valor_pago">Valor Pago</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_pago" name="valor_pago" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="forma_pagamento">Forma de Pagamento</label>
                            <select class="form-control" id="forma_pagamento" name="forma_pagamento" required>
                                <option value="">Selecione</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Cartão de Crédito">Cartão de Crédito</option>
                                <option value="Cartão de Débito">Cartão de Débito</option>
                                <option value="PIX">PIX</option>
                                <option value="Transferência Bancária">Transferência Bancária</option>
                                <option value="Boleto">Boleto</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="garantia_meses">Garantia (meses)</label>
                            <input type="number" class="form-control" id="garantia_meses" name="garantia_meses" min="0" value="3">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Técnicos que realizaram o serviço</label>
                        <div class="tecnicos-container">
                            <?php foreach ($tecnicos as $tecnico): ?>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="tecnico_<?= $tecnico['id'] ?>" name="tecnicos[]" value="<?= $tecnico['id'] ?>">
                                <label class="custom-control-label" for="tecnico_<?= $tecnico['id'] ?>"><?= $tecnico['nome'] ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes_finalizacao">Observações da Finalização</label>
                        <textarea class="form-control" id="observacoes_finalizacao" name="observacoes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Finalizar Agendamento</button>
                    <button type="button" class="btn btn-primary" id="btnGerarPDF">
                        <i class="fas fa-file-pdf"></i> Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

   <!-- JavaScript -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>
   <script>
       $(document).ready(function() {
           // Inicializar calendário
           var calendarEl = document.getElementById('calendar');
           var calendar = new FullCalendar.Calendar(calendarEl, {
               initialView: 'dayGridMonth',
               headerToolbar: {
                   left: 'prev,next today',
                   center: 'title',
                   right: 'dayGridMonth,timeGridWeek,listWeek'
               },
               locale: 'pt-br',
               buttonText: {
                   today: 'Hoje',
                   month: 'Mês',
                   week: 'Semana',
                   list: 'Lista'
               },
               height: 600,
               events: function(fetchInfo, successCallback, failureCallback) {
                   // Converter agendamentos PHP para eventos do calendário
                   var events = [];
                   <?php foreach ($agendamentos as $agendamento): ?>
                   events.push({
                       id: '<?= $agendamento['id'] ?>',
                       title: '<?= addslashes($agendamento['cliente_nome']) ?> - <?= addslashes($agendamento['servico_nome']) ?>',
                       start: '<?= $agendamento['data_agendamento'] ?>T<?= $agendamento['hora_inicio'] ?>',
                       <?php if (isset($agendamento['hora_fim']) && $agendamento['hora_fim']): ?>
                       end: '<?= $agendamento['data_agendamento'] ?>T<?= $agendamento['hora_fim'] ?>',
                       <?php endif; ?>
                       backgroundColor: '<?php
                           switch ($agendamento['status']) {
                               case 'pendente': echo '#f59e0b'; break;
                               case 'concluido': echo '#10b981'; break;
                               case 'cancelado': echo '#ef4444'; break;
                               default: echo '#3b82f6';
                           }
                       ?>',
                       borderColor: '<?php
                           switch ($agendamento['status']) {
                               case 'pendente': echo '#f59e0b'; break;
                               case 'concluido': echo '#10b981'; break;
                               case 'cancelado': echo '#ef4444'; break;
                               default: echo '#3b82f6';
                           }
                       ?>',
                       extendedProps: {
                           status: '<?= $agendamento['status'] ?>',
                           cliente: '<?= addslashes($agendamento['cliente_nome']) ?>',
                           servico: '<?= addslashes($agendamento['servico_nome']) ?>',
                           tecnico: '<?= addslashes($agendamento['tecnico_nome']) ?>'
                       }
                   });
                   <?php endforeach; ?>
                   successCallback(events);
               },
               eventClick: function(info) {
                   alert('Agendamento: ' + info.event.title + '\nStatus: ' + info.event.extendedProps.status);
               },
               eventDidMount: function(info) {
                   // Adicionar tooltip
                   $(info.el).tooltip({
                       title: info.event.title + '\nTécnico: ' + info.event.extendedProps.tecnico + '\nStatus: ' + info.event.extendedProps.status,
                       placement: 'top',
                       trigger: 'hover'
                   });
               }
           });
           
           calendar.render();
           
           // Aplicar filtros
           $('#filterForm').on('submit', function(e) {
               e.preventDefault();
               
               const tecnico_id = $('#tecnico_id').val();
               const servico_id = $('#servico_id').val();
               const status = $('#status').val();
               
               let url = 'admin-calendario.php?';
               if (tecnico_id) url += `tecnico_id=${tecnico_id}&`;
               if (servico_id) url += `servico_id=${servico_id}&`;
               if (status) url += `status=${status}&`;
               
               window.location.href = url;
           });
           
           // Limpar filtros
           $('#resetFilters').on('click', function() {
               window.location.href = 'admin-calendario.php';
           });
       });
   </script>

<style>
    /* Estilos para os eventos no calendário */
    .fc-event {
        cursor: pointer;
        padding: 2px;
    }
    
    .fc-event-content-wrapper {
        padding: 2px;
        overflow: hidden;
    }
    
    .fc-event-time {
        font-weight: bold;
        font-size: 0.85em;
    }
    
    .fc-event-title {
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .fc-event-cliente {
        font-size: 0.8em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .fc-event-status {
        font-size: 0.7em;
        margin-top: 2px;
        display: inline-block;
    }
    
    /* Estilo para contagem de agendamentos */
    .event-count {
        position: relative;
    }
    
    .fc-daygrid-day-number {
        position: relative;
        z-index: 4;
    }
    
    .fc-daygrid-day-events {
        position: relative;
    }
    
    .fc-daygrid-day-events::after {
        content: attr(data-count);
        position: absolute;
        top: 0;
        right: 5px;
        background-color: #4e73df;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        z-index: 3;
    }
    
    /* Estilos para o modal de finalização */
    .tecnicos-container {
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
    }
    
    /* Responsividade para dispositivos móveis */
    @media (max-width: 768px) {
        .fc-header-toolbar {
            flex-direction: column;
        }
        
        .fc-header-toolbar .fc-toolbar-chunk {
            margin-bottom: 10px;
        }
        
        .fc-event-content-wrapper {
            padding: 1px;
        }
        
        .fc-event-time, .fc-event-cliente, .fc-event-status {
            display: none;
        }
        
        /* Ajustes para navbar responsivo */
        .admin-sidebar {
            width: 100%;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1050;
        }
        
        .admin-sidebar.show {
            transform: translateX(0);
        }
        
        .admin-content {
            margin-left: 0;
            width: 100%;
        }
    }
</style>

<?php
// Incluir o footer
include 'views/admin/includes/footer.php';
?>
