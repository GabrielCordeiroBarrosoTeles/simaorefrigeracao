<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Calendário de Agendamentos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/tecnico">Dashboard</a></li>
                    <li class="breadcrumb-item active">Calendário</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php
        // Exibir mensagem flash
        $flash_message = get_flash_message();
        if ($flash_message) {
            echo '<div class="alert alert-' . $flash_message['type'] . '">' . $flash_message['message'] . '</div>';
        }
        ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Calendário de Agendamentos</h3>
                        <div class="card-tools">
                            <a href="/tecnico" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Voltar para o Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Legenda</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="width: 20px; height: 20px; background-color: #f59e0b; border-radius: 4px; margin-right: 10px;"></div>
                                    <span>Pendente</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="width: 20px; height: 20px; background-color: #10b981; border-radius: 4px; margin-right: 10px;"></div>
                                    <span>Concluído</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="width: 20px; height: 20px; background-color: #ef4444; border-radius: 4px; margin-right: 10px;"></div>
                                    <span>Cancelado</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FullCalendar JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        height: 'auto',
        events: '/tecnico/api?action=agendamentos',
        eventClick: function(info) {
            window.location.href = '/tecnico/agendamento?id=' + info.event.id;
        },
        eventClassNames: function(arg) {
            return ['status-' + arg.event.extendedProps.status];
        }
    });
    
    calendar.render();
});
</script>

<style>
    /* Estilos para o calendário */
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 4px;
    }
    .fc-daygrid-event {
        white-space: normal;
    }
    .fc-day-today {
        background-color: rgba(37, 99, 235, 0.05) !important;
    }
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 600;
    }
    .fc-button-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
    }
    .fc-button-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
    .fc-button-active {
        background-color: #1e40af !important;
        border-color: #1e40af !important;
    }
    
    /* Estilos para status */
    .status-pendente {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
    }
    .status-concluido {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
    }
    .status-cancelado {
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
    }
</style>

<?php require 'views/admin/includes/footer.php'; ?>
