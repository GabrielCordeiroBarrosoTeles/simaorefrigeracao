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
                            <label>Período</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="date" id="data-inicio" class="form-control" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="date" id="data-fim" class="form-control" value="<?= date('Y-m-t') ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button id="btn-filtrar" class="btn btn-primary btn-block">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Legenda</h3>
                    </div>
                    <div class="card-body p-2">
                        <div class="d-flex align-items-center mb-2 p-2 rounded" style="background-color: rgba(245, 158, 11, 0.1);">
                            <div style="width: 20px; height: 20px; background-color: #f59e0b; margin-right: 10px; border-radius: 4px;"></div>
                            <span>Pendente</span>
                        </div>
                        <div class="d-flex align-items-center mb-2 p-2 rounded" style="background-color: rgba(16, 185, 129, 0.1);">
                            <div style="width: 20px; height: 20px; background-color: #10b981; margin-right: 10px; border-radius: 4px;"></div>
                            <span>Concluído</span>
                        </div>
                        <div class="d-flex align-items-center p-2 rounded" style="background-color: rgba(239, 68, 68, 0.1);">
                            <div style="width: 20px; height: 20px; background-color: #ef4444; margin-right: 10px; border-radius: 4px;"></div>
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
                        <a href="/" target="_blank" class="btn btn-secondary btn-block mt-2">
                            <i class="fas fa-external-link-alt mr-2"></i> Ver Site
                        </a>
                    </div>
                </div>
                
                <div class="card d-none d-md-block">
                    <div class="card-header">
                        <h3 class="card-title">Próximos Agendamentos</h3>
                    </div>
                    <div class="card-body p-0">
                        <div id="proximos-agendamentos" class="list-group list-group-flush">
                            <!-- Preenchido via JavaScript -->
                            <div class="list-group-item text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Carregando...</span>
                                </div>
                            </div>
                        </div>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-agendamento-label">Detalhes do Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Data</label>
                            <input type="text" id="agendamento-data" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Horário</label>
                            <input type="text" id="agendamento-horario" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <div id="agendamento-status-container">
                                <span id="agendamento-status" class="badge badge-pill badge-primary">Status</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Observações</label>
                    <textarea id="agendamento-observacoes" class="form-control" rows="3" readonly></textarea>
                </div>
                <div id="agendamento-acoes" class="mt-3 border-top pt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Atualizar Status</label>
                                <select id="agendamento-novo-status" class="form-control">
                                    <option value="pendente">Pendente</option>
                                    <option value="concluido">Concluído</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button id="btn-atualizar-status" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Salvar Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="btn-editar-agendamento" class="btn btn-primary">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
                <button type="button" id="btn-excluir-agendamento" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i> Excluir
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Novo Agendamento Rápido -->
<div class="modal fade" id="modal-novo-agendamento" tabindex="-1" role="dialog" aria-labelledby="modal-novo-agendamento-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-novo-agendamento-label">Novo Agendamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-novo-agendamento">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="novo-titulo">Título*</label>
                                <input type="text" id="novo-titulo" name="titulo" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="novo-cliente">Cliente*</label>
                                <select id="novo-cliente" name="cliente_id" class="form-control" required>
                                    <option value="">Selecione um cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>"><?= $cliente['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="novo-servico">Serviço*</label>
                                <select id="novo-servico" name="servico_id" class="form-control" required>
                                    <option value="">Selecione um serviço</option>
                                    <?php foreach ($servicos as $servico): ?>
                                    <option value="<?= $servico['id'] ?>"><?= $servico['titulo'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="novo-tecnico">Técnico*</label>
                                <select id="novo-tecnico" name="tecnico_id" class="form-control" required>
                                    <option value="">Selecione um técnico</option>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?= $tecnico['id'] ?>"><?= $tecnico['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="novo-data">Data*</label>
                                <input type="date" id="novo-data" name="data_agendamento" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="novo-hora-inicio">Hora Início*</label>
                                        <input type="time" id="novo-hora-inicio" name="hora_inicio" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="novo-hora-fim">Hora Fim</label>
                                        <input type="time" id="novo-hora-fim" name="hora_fim" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="novo-observacoes">Observações</label>
                        <textarea id="novo-observacoes" name="observacoes" class="form-control" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="status" value="pendente">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FullCalendar JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/pt-br.js"></script>

<style>
    /* Estilos personalizados para o calendário */
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
    
    /* Estilos para o modal */
    #agendamento-status-container {
        display: flex;
        align-items: center;
        height: 38px;
    }
    #agendamento-status {
        font-size: 14px;
        padding: 8px 16px;
    }
    .badge-pendente {
        background-color: #f59e0b;
    }
    .badge-concluido {
        background-color: #10b981;
    }
    .badge-cancelado {
        background-color: #ef4444;
    }
    
    /* Estilos para a lista de próximos agendamentos */
    .agendamento-item {
        border-left: 4px solid #2563eb;
        transition: all 0.2s ease;
    }
    .agendamento-item:hover {
        background-color: #f9fafb;
    }
    .agendamento-item.status-pendente {
        border-left-color: #f59e0b;
    }
    .agendamento-item.status-concluido {
        border-left-color: #10b981;
    }
    .agendamento-item.status-cancelado {
        border-left-color: #ef4444;
    }
    .agendamento-data {
        font-size: 12px;
        color: #6b7280;
    }
    .agendamento-titulo {
        font-weight: 600;
        margin-bottom: 2px;
    }
    .agendamento-cliente {
        font-size: 13px;
        color: #4b5563;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .fc-toolbar.fc-header-toolbar {
            flex-direction: column;
        }
        .fc-toolbar-chunk {
            margin-bottom: 10px;
        }
        .fc-daygrid-event {
            font-size: 12px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentAgendamentoId = null;
    let calendar = null;
    
    // Inicializar o calendário
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
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
        eventClassNames: function(arg) {
            return ['status-' + arg.event.extendedProps.status];
        },
        eventClick: function(info) {
            showAgendamentoDetails(info.event);
        },
        select: function(info) {
            // Preencher o formulário com a data selecionada
            document.getElementById('novo-data').value = info.startStr;
            
            // Definir horário padrão (8h às 10h)
            document.getElementById('novo-hora-inicio').value = '08:00';
            document.getElementById('novo-hora-fim').value = '10:00';
            
            // Abrir modal de novo agendamento
            $('#modal-novo-agendamento').modal('show');
        },
        eventDidMount: function(info) {
            // Adicionar tooltip
            $(info.el).tooltip({
                title: info.event.title + ' - ' + info.event.extendedProps.cliente,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        },
        datesSet: function(dateInfo) {
            // Atualizar os campos de data do filtro
            if (dateInfo.view.type === 'dayGridMonth') {
                const startDate = new Date(dateInfo.start);
                const endDate = new Date(dateInfo.end);
                endDate.setDate(endDate.getDate() - 1);
                
                document.getElementById('data-inicio').value = formatDate(startDate);
                document.getElementById('data-fim').value = formatDate(endDate);
                
                // Carregar próximos agendamentos
                loadProximosAgendamentos();
            }
        }
    });
    
    calendar.render();
    
    // Carregar próximos agendamentos
    loadProximosAgendamentos();
    
    // Função para mostrar detalhes do agendamento
    function showAgendamentoDetails(event) {
        currentAgendamentoId = event.id;
        
        // Preencher modal com dados do evento
        document.getElementById('agendamento-titulo').value = event.title;
        document.getElementById('agendamento-cliente').value = event.extendedProps.cliente;
        document.getElementById('agendamento-tecnico').value = event.extendedProps.tecnico;
        
        // Formatar data
        const dataHora = new Date(event.start);
        document.getElementById('agendamento-data').value = dataHora.toLocaleDateString('pt-BR');
        
        // Formatar horário
        let horario = dataHora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        if (event.end) {
            const horaFim = new Date(event.end).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            horario += ' às ' + horaFim;
        }
        document.getElementById('agendamento-horario').value = horario;
        
        // Observações (se disponível)
        const observacoes = document.getElementById('agendamento-observacoes');
        observacoes.value = event.extendedProps.observacoes || 'Nenhuma observação registrada.';
        
        // Status
        const statusBadge = document.getElementById('agendamento-status');
        statusBadge.textContent = capitalizeFirstLetter(event.extendedProps.status);
        statusBadge.className = 'badge badge-pill badge-' + event.extendedProps.status;
        
        // Atualizar select de status
        document.getElementById('agendamento-novo-status').value = event.extendedProps.status;
        
        // Configurar link de edição
        document.getElementById('btn-editar-agendamento').href = '/admin/agendamentos/editar?id=' + event.id;
        
        // Abrir modal
        $('#modal-agendamento').modal('show');
    }
    
    // Atualizar status do agendamento
    document.getElementById('btn-atualizar-status').addEventListener('click', function() {
        if (!currentAgendamentoId) return;
        
        const novoStatus = document.getElementById('agendamento-novo-status').value;
        
        // Fazer requisição AJAX para atualizar o status
        fetch('/admin/agendamentos/api?action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: currentAgendamentoId,
                status: novoStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar o status no modal
                const statusBadge = document.getElementById('agendamento-status');
                statusBadge.textContent = capitalizeFirstLetter(novoStatus);
                statusBadge.className = 'badge badge-pill badge-' + novoStatus;
                
                // Atualizar o evento no calendário
                const event = calendar.getEventById(currentAgendamentoId);
                if (event) {
                    event.setExtendedProp('status', novoStatus);
                    event.setProp('classNames', ['status-' + novoStatus]);
                }
                
                // Mostrar mensagem de sucesso
                alert('Status atualizado com sucesso!');
                
                // Recarregar próximos agendamentos
                loadProximosAgendamentos();
            } else {
                alert('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar status. Verifique o console para mais detalhes.');
        });
    });
    
    // Excluir agendamento
    document.getElementById('btn-excluir-agendamento').addEventListener('click', function() {
        if (!currentAgendamentoId) return;
        
        if (confirm('Tem certeza que deseja excluir este agendamento?')) {
            // Fazer requisição AJAX para excluir o agendamento
            fetch('/admin/agendamentos/api?action=delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: currentAgendamentoId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover o evento do calendário
                    const event = calendar.getEventById(currentAgendamentoId);
                    if (event) {
                        event.remove();
                    }
                    
                    // Fechar o modal
                    $('#modal-agendamento').modal('hide');
                    
                    // Mostrar mensagem de sucesso
                    alert('Agendamento excluído com sucesso!');
                    
                    // Recarregar próximos agendamentos
                    loadProximosAgendamentos();
                } else {
                    alert('Erro ao excluir agendamento: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao excluir agendamento. Verifique o console para mais detalhes.');
            });
        }
    });
    
    // Filtrar eventos
    document.getElementById('btn-filtrar').addEventListener('click', function() {
        const tecnicoId = document.getElementById('filtro-tecnico').value;
        const statusPendente = document.getElementById('status-pendente').checked;
        const statusConcluido = document.getElementById('status-concluido').checked;
        const statusCancelado = document.getElementById('status-cancelado').checked;
        const dataInicio = document.getElementById('data-inicio').value;
        const dataFim = document.getElementById('data-fim').value;
        
        // Recarregar eventos com filtros
        calendar.getEvents().forEach(event => event.remove());
        
        // Construir URL com filtros
        let url = '/admin/agendamentos/api?action=get';
        if (tecnicoId) url += '&tecnico_id=' + tecnicoId;
        if (!statusPendente || !statusConcluido || !statusCancelado) {
            const statusArray = [];
            if (statusPendente) statusArray.push('pendente');
            if (statusConcluido) statusArray.push('concluido');
            if (statusCancelado) statusArray.push('cancelado');
            url += '&status=' + statusArray.join(',');
        }
        if (dataInicio) url += '&start=' + dataInicio;
        if (dataFim) url += '&end=' + dataFim;
        
        // Carregar eventos filtrados
        fetch(url)
            .then(response => response.json())
            .then(data => {
                data.forEach(eventData => {
                    calendar.addEvent({
                        id: eventData.id,
                        title: eventData.title,
                        start: eventData.start,
                        end: eventData.end,
                        color: eventData.color,
                        extendedProps: eventData.extendedProps,
                        classNames: ['status-' + eventData.extendedProps.status]
                    });
                });
            })
            .catch(error => {
                console.error('Erro ao carregar eventos:', error);
                alert('Erro ao carregar eventos. Verifique o console para mais detalhes.');
            });
    });
    
    // Formulário de novo agendamento
    document.getElementById('form-novo-agendamento').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const agendamentoData = {};
        
        // Converter FormData para objeto
        for (const [key, value] of formData.entries()) {
            agendamentoData[key] = value;
        }
        
        // Fazer requisição AJAX para criar o agendamento
        fetch('/admin/agendamentos/api?action=add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(agendamentoData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Adicionar o evento ao calendário
                calendar.addEvent({
                    id: data.id,
                    title: agendamentoData.titulo,
                    start: agendamentoData.data_agendamento + 'T' + agendamentoData.hora_inicio,
                    end: agendamentoData.data_agendamento + 'T' + (agendamentoData.hora_fim || '23:59'),
                    extendedProps: {
                        cliente: $('#novo-cliente option:selected').text(),
                        tecnico: $('#novo-tecnico option:selected').text(),
                        status: 'pendente',
                        observacoes: agendamentoData.observacoes
                    },
                    classNames: ['status-pendente']
                });
                
                // Fechar o modal
                $('#modal-novo-agendamento').modal('hide');
                
                // Limpar o formulário
                document.getElementById('form-novo-agendamento').reset();
                
                // Mostrar mensagem de sucesso
                alert('Agendamento criado com sucesso!');
                
                // Recarregar próximos agendamentos
                loadProximosAgendamentos();
            } else {
                alert('Erro ao criar agendamento: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao criar agendamento. Verifique o console para mais detalhes.');
        });
    });
    
    // Carregar próximos agendamentos
    function loadProximosAgendamentos() {
        const proximosContainer = document.getElementById('proximos-agendamentos');
        proximosContainer.innerHTML = '<div class="list-group-item text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Carregando...</span></div></div>';
        
        fetch('/admin/agendamentos/api?action=get&limit=5&sort=asc&start=' + formatDate(new Date()))
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    proximosContainer.innerHTML = '<div class="list-group-item text-center py-3">Nenhum agendamento próximo</div>';
                    return;
                }
                
                let html = '';
                data.forEach(event => {
                    const dataHora = new Date(event.start);
                    const dataFormatada = dataHora.toLocaleDateString('pt-BR');
                    const horaFormatada = dataHora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                    
                    html += `
                        <a href="#" class="list-group-item list-group-item-action agendamento-item status-${event.extendedProps.status}" 
                           data-id="${event.id}" onclick="showAgendamentoById(${event.id}); return false;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="agendamento-data">${dataFormatada} às ${horaFormatada}</div>
                                <span class="badge badge-pill badge-${event.extendedProps.status}">${capitalizeFirstLetter(event.extendedProps.status)}</span>
                            </div>
                            <div class="agendamento-titulo">${event.title}</div>
                            <div class="agendamento-cliente">${event.extendedProps.cliente}</div>
                        </a>
                    `;
                });
                
                proximosContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar próximos agendamentos:', error);
                proximosContainer.innerHTML = '<div class="list-group-item text-center py-3 text-danger">Erro ao carregar agendamentos</div>';
            });
    }
    
    // Função para formatar data
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Função para capitalizar primeira letra
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    // Função global para mostrar agendamento por ID
    window.showAgendamentoById = function(id) {
        const event = calendar.getEventById(id);
        if (event) {
            showAgendamentoDetails(event);
        }
    };
});
</script>

<?php require 'views/admin/includes/footer.php'; ?>
