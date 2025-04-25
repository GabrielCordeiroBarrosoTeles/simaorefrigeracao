<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Agendamentos do Técnico: <?= $tecnico['nome'] ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/tecnicos">Técnicos</a></li>
                    <li class="breadcrumb-item active">Agendamentos</li>
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
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informações do Técnico</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <img class="img-circle elevation-2" src="https://ui-avatars.com/api/?name=<?= urlencode($tecnico['nome']) ?>&background=<?= urlencode(str_replace('#', '', $tecnico['cor'])) ?>&color=fff&size=128" alt="Foto do Técnico" style="width: 100px; height: 100px;">
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Nome:</strong> <?= $tecnico['nome'] ?></p>
                                        <p><strong>Email:</strong> <?= $tecnico['email'] ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Telefone:</strong> <?= $tecnico['telefone'] ?></p>
                                        <p><strong>Especialidade:</strong> <?= $tecnico['especialidade'] ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Status:</strong> 
                                            <?php if ($tecnico['status'] == 'ativo'): ?>
                                                <span class="badge badge-success">Ativo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inativo</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Cor:</strong> <span class="badge" style="background-color: <?= $tecnico['cor'] ?>; color: white;"><?= $tecnico['cor'] ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Agendamentos do Técnico</h3>
                <div class="card-tools">
                    <a href="/admin/agendamentos/novo?tecnico_id=<?= $tecnico['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Novo Agendamento
                    </a>
                    <a href="/admin/tecnicos" class="btn btn-secondary btn-sm ml-2">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($agendamentos)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> Nenhum agendamento encontrado para este técnico.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Data</th>
                                    <th>Horário</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?= $agendamento['id'] ?></td>
                                        <td><?= $agendamento['titulo'] ?></td>
                                        <td><?= $agendamento['cliente_nome'] ?></td>
                                        <td><?= $agendamento['servico_nome'] ?></td>
                                        <td><?= format_date($agendamento['data_agendamento'], 'd/m/Y') ?></td>
                                        <td>
                                            <?= substr($agendamento['hora_inicio'], 0, 5) ?>
                                            <?php if (!empty($agendamento['hora_fim'])): ?>
                                                - <?= substr($agendamento['hora_fim'], 0, 5) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($agendamento['status'] == 'pendente'): ?>
                                                <span class="badge badge-warning">Pendente</span>
                                            <?php elseif ($agendamento['status'] == 'concluido'): ?>
                                                <span class="badge badge-success">Concluído</span>
                                            <?php elseif ($agendamento['status'] == 'cancelado'): ?>
                                                <span class="badge badge-danger">Cancelado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="/admin/agendamentos/editar?id=<?= $agendamento['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="/admin/agendamentos/excluir?id=<?= $agendamento['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este agendamento?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Calendário de Agendamentos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Calendário de Agendamentos</h3>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
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
        events: '/admin/agendamentos/api?action=get&tecnico_id=<?= $tecnico['id'] ?>',
        eventClick: function(info) {
            window.location.href = '/admin/agendamentos/editar?id=' + info.event.id;
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
