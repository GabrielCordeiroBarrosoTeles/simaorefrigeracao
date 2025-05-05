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
    $stmt = $db->prepare("SELECT id, nome, cor FROM tecnicos WHERE status = 'ativo' ORDER BY nome");
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

// Título da página
$page_title = "Calendário de Agendamentos";
$page_icon = "calendar-alt";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Painel Administrativo | <?= SITE_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'views/admin/includes/sidebar.php'; ?>
        
        <!-- Content -->
        <div class="content-wrapper">
            <!-- Header -->
            <?php include 'views/admin/includes/header.php'; ?>
            
            <!-- Main Content -->
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-<?= $page_icon ?> mr-2"></i> <?= $page_title ?>
                    </h1>
                    <a href="admin-form.php?form=agendamento" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Novo Agendamento
                    </a>
                </div>
                
                <?php display_flash_message(); ?>
                
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Calendário de Agendamentos</h6>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
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
                    <div class="card-body">
                        <div id="calendar" data-source="/admin-calendario-json.php"></div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include 'views/admin/includes/footer.php'; ?>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>
    <script src="assets/js/admin.js"></script>
    
    <script>
        $(document).ready(function() {
            // Aplicar filtros
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                
                const tecnico_id = $('#tecnico_id').val();
                const servico_id = $('#servico_id').val();
                const status = $('#status').val();
                
                let url = '/admin-calendario-json.php?';
                if (tecnico_id) url += `tecnico_id=${tecnico_id}&`;
                if (servico_id) url += `servico_id=${servico_id}&`;
                if (status) url += `status=${status}&`;
                
                // Atualizar fonte de dados do calendário
                const calendarApi = calendar.getApi();
                calendarApi.removeAllEventSources();
                calendarApi.addEventSource(url);
            });
            
            // Limpar filtros
            $('#resetFilters').on('click', function() {
                $('#tecnico_id').val('');
                $('#servico_id').val('');
                $('#status').val('');
                
                // Atualizar fonte de dados do calendário
                const calendarApi = calendar.getApi();
                calendarApi.removeAllEventSources();
                calendarApi.addEventSource('/admin-calendario-json.php');
            });
            
            // Inicializar calendário
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
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
                events: '/admin-calendario-json.php',
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        return false;
                    }
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>
