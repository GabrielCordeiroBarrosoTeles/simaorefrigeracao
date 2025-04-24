<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Calendário de Agendamentos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/agendamentos">Agendamentos</a></li>
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
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filtros</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Técnicos</label>
                            <select id="filtro-tecnico" class="form-control">
                                <option value="">Todos os técnicos</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                <option value="<?= $tecnico['id'] ?>"><?= $tecnico['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Status</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="status-pendente" checked>
                                <label class="custom-control-label" for="status-pendente">Pendente</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="status-concluido" checked>
                                <label class="custom-control-label" for="status-concluido">Concluído</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="status-cancelado" checked>
                                <label class="custom-control-label" for="status-cancelado">Cancelado</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button id="btn-filtrar" class="btn btn-primary btn-block">Filtrar</button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Legenda</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 20px; height: 20px; background-color: #f59e0b; margin-right: 10px;"></div>
                            <span>Pendente</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div style="width: 20px; height: 20px; background-color: #10b981; margin-right: 10px;"></div>
                            <span>Concluído</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="width: 20px; height: 20px; background-color: #ef4444; margin-right: 10px;"></div>
                            <span>Cancelado</span>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ações</h3>
                    </div>
                    <div class="card-body">
                        <a href="/admin/agendamentos/novo" class="btn btn-success btn-block">
                            <i class="fas fa-plus mr-2"></i> Novo Agendamento
                        </a>
                        <a href="/admin/agendamentos" class="btn btn-info btn-block mt-2">
                            <i class="fas fa-list mr-2"></i> Lista de Agendamentos
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Detalhes do Agendamento -->
<div class="modal fade" id="modal-agendamento" tabindex="-1" role="dialog" aria-labelledby="modal-agendamento-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-agendamento-label">Detalhes do Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" id="agendamento-titulo" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Cliente</label>
                    <input type="text" id="agendamento-cliente" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Técnico</label>
                    <input type="text" id="agendamento-tecnico" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Data e Hora</label>
                    <input type="text" id="agendamento-data" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <input type="text" id="agendamento-status" class="form-control" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="btn-editar-agendamento" class="btn btn-primary">Editar</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

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
        selectable: true,
        selectMirror: true,
        navLinks: true,
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5], // Segunda a sexta
            startTime: '08:00',
            endTime: '18:00',
        },
        events: '/admin/agendamentos/api?action=get',
        eventClick: function(info) {
            // Preencher modal com dados do evento
            document.getElementById('agendamento-titulo').value = info.event.title;
            document.getElementById('agendamento-cliente').value = info.event.extendedProps.cliente;
            document.getElementById('agendamento-tecnico').value = info.event.extendedProps.tecnico;
            document.getElementById('agendamento-data').value = info.event.start.toLocaleString();
            document.getElementById('agendamento-status').value = info.event.extendedProps.status;
            
            // Configurar link de edição
            document.getElementById('btn-editar-agendamento').href = '/admin/agendamentos/editar?id=' + info.event.id;
            
            // Abrir modal
            $('#modal-agendamento').modal('show');
        },
        select: function(info) {
            // Redirecionar para página de novo agendamento com a data pré-selecionada
            window.location.href = '/admin/agendamentos/novo?data=' + info.startStr;
        }
    });
    
    calendar.render();
    
    // Filtros
    document.getElementById('btn-filtrar').addEventListener('click', function() {
        var tecnicoId = document.getElementById('filtro-tecnico').value;
        var statusPendente = document.getElementById('status-pendente').checked;
        var statusConcluido = document.getElementById('status-concluido').checked;
        var statusCancelado = document.getElementById('status-cancelado').checked;
        
        // Filtrar eventos
        calendar.getEvents().forEach(function(event) {
            var visible = true;
            
            // Filtrar por técnico
            if (tecnicoId && event.extendedProps.tecnico_id != tecnicoId) {
                visible = false;
            }
            
            // Filtrar por status
            if (!statusPendente && event.extendedProps.status === 'pendente') {
                visible = false;
            }
            if (!statusConcluido && event.extendedProps.status === 'concluido') {
                visible = false;
            }
            if (!statusCancelado && event.extendedProps.status === 'cancelado') {
                visible = false;
            }
            
            // Aplicar visibilidade
            event.setProp('display', visible ? 'auto' : 'none');
        });
    });
});
</script>

<?php require 'views/admin/includes/footer.php'; ?>
