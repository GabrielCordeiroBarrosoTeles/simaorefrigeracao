<?php require_once 'views/admin/includes/header.php'; ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Calendário de Agendamentos</h1>
        <a href="/simaorefrigeracao/admin/agendamentos/novo" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Novo Agendamento
        </a>
    </div>

    <?php display_flash_message(); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Calendário</h6>
            <div class="dropdown no-arrow">
                <a href="/simaorefrigeracao/admin/agendamentos" class="btn btn-sm btn-info">
                    <i class="fas fa-list fa-sm"></i> Ver Lista
                </a>
            </div>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal de detalhes do agendamento -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Detalhes do Agendamento</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="eventDetails">
                    <p><strong>Título:</strong> <span id="eventTitle"></span></p>
                    <p><strong>Cliente:</strong> <span id="eventClient"></span></p>
                    <p><strong>Serviço:</strong> <span id="eventService"></span></p>
                    <p><strong>Técnico:</strong> <span id="eventTechnician"></span></p>
                    <p><strong>Data:</strong> <span id="eventDate"></span></p>
                    <p><strong>Horário:</strong> <span id="eventTime"></span></p>
                    <p><strong>Status:</strong> <span id="eventStatus"></span></p>
                    <p><strong>Observações:</strong> <span id="eventNotes"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Fechar</button>
                <a class="btn btn-primary" id="editEvent" href="#">Editar</a>
            </div>
        </div>
    </div>
</div>

<!-- Adicionar FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'pt-br',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '/simaorefrigeracao/admin/agendamentos/api',
            eventClick: function(info) {
                // Preencher o modal com os detalhes do evento
                $('#eventTitle').text(info.event.title);
                $('#eventClient').text(info.event.extendedProps.cliente || 'Não informado');
                $('#eventService').text(info.event.extendedProps.servico || 'Não informado');
                $('#eventTechnician').text(info.event.extendedProps.tecnico || 'Não atribuído');
                $('#eventDate').text(info.event.start.toLocaleDateString('pt-BR'));
                
                var startTime = info.event.start.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
                var endTime = info.event.end ? info.event.end.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'}) : '';
                $('#eventTime').text(startTime + (endTime ? ' - ' + endTime : ''));
                
                $('#eventStatus').html('<span class="badge badge-' + getStatusClass(info.event.extendedProps.status) + '">' + 
                    (info.event.extendedProps.status ? info.event.extendedProps.status.charAt(0).toUpperCase() + info.event.extendedProps.status.slice(1) : 'Pendente') + '</span>');
                
                $('#eventNotes').text(info.event.extendedProps.observacoes || 'Nenhuma observação');
                
                // Configurar o link de edição
                $('#editEvent').attr('href', '/simaorefrigeracao/admin/agendamentos/editar?id=' + info.event.id);
                
                // Mostrar o modal
                $('#eventModal').modal('show');
            }
        });
        
        calendar.render();
        
        function getStatusClass(status) {
            switch (status) {
                case 'pendente': return 'warning';
                case 'confirmado': return 'primary';
                case 'concluido': return 'success';
                case 'cancelado': return 'danger';
                default: return 'secondary';
            }
        }
    });
</script>

<style>
    #calendar {
        margin: 0 auto;
        max-width: 100%;
    }
    .fc-event {
        cursor: pointer;
    }
</style>

<?php require_once 'views/admin/includes/footer.php'; ?>
