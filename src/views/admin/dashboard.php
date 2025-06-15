<?php require 'views/admin/includes/header.php'; ?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Dashboard</li>
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
        
        <!-- Estatísticas -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $stats['clientes'] ?></h3>
                        <p>Clientes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['agendamentos'] ?></h3>
                        <p>Agendamentos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['tecnicos'] ?></h3>
                        <p>Técnicos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-hard-hat"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['servicos'] ?></h3>
                        <p>Serviços</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Agendamentos de Hoje -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-day mr-1"></i>
                            Agendamentos de Hoje (<?= $stats['agendamentos_hoje'] ?>)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Horário</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Técnico</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $agendamentos_hoje = array_filter($proximos_agendamentos, function($a) {
                                    return date('Y-m-d', strtotime($a['data_agendamento'])) == date('Y-m-d');
                                });
                                
                                if (empty($agendamentos_hoje)): 
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum agendamento para hoje.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($agendamentos_hoje as $agendamento): ?>
                                    <tr>
                                        <td><?= date('H:i', strtotime($agendamento['hora_inicio'])) ?></td>
                                        <td><?= $agendamento['cliente_nome'] ?></td>
                                        <td><?= $agendamento['servico_nome'] ?></td>
                                        <td><?= $agendamento['tecnico_nome'] ?></td>
                                        <td>
                                            <?php
                                            $status_class = 'secondary';
                                            switch ($agendamento['status']) {
                                                case 'pendente':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'concluido':
                                                    $status_class = 'success';
                                                    break;
                                                case 'cancelado':
                                                    $status_class = 'danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?= $status_class ?>">
                                                <?= ucfirst($agendamento['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/admin/agendamentos" class="btn btn-sm btn-primary">Ver Todos</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-week mr-1"></i>
                            Próximos Agendamentos
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Técnico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $proximos = array_filter($proximos_agendamentos, function($a) {
                                    return date('Y-m-d', strtotime($a['data_agendamento'])) > date('Y-m-d');
                                });
                                
                                if (empty($proximos)): 
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum agendamento futuro.</td>
                                </tr>
                                <?php else: ?>
                                    <?php 
                                    $count = 0;
                                    foreach ($proximos as $agendamento): 
                                        if ($count >= 5) break;
                                        $count++;
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></td>
                                        <td><?= $agendamento['cliente_nome'] ?></td>
                                        <td><?= $agendamento['servico_nome'] ?></td>
                                        <td><?= $agendamento['tecnico_nome'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/admin/agendamentos/calendario" class="btn btn-sm btn-primary">Ver Calendário</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Clientes e Contatos -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Clientes Recentes
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($clientes_recentes)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum cliente cadastrado.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($clientes_recentes as $cliente): ?>
                                    <tr>
                                        <td><?= $cliente['nome'] ?></td>
                                        <td><?= $cliente['email'] ?></td>
                                        <td><?= $cliente['telefone'] ?></td>
                                        <td><?= ucfirst($cliente['tipo']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/admin/clientes" class="btn btn-sm btn-primary">Ver Todos</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-envelope mr-1"></i>
                            Contatos Novos (<?= $stats['contatos_novos'] ?>)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                            <a href="/admin/contatos" class="btn btn-lg btn-primary">
                                <i class="fas fa-envelope mr-2"></i> Ver Contatos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calendário Resumido -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar mr-1"></i>
                            Calendário de Agendamentos
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="calendar-mini"></div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="/admin/agendamentos/calendario" class="btn btn-primary">Ver Calendário Completo</a>
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
    var calendarEl = document.getElementById('calendar-mini');
    
    var calendar = new FullCal  {
    var calendarEl = document.getElementById('calendar-mini');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        height: 450,
        events: '/admin/agendamentos/api?action=get',
        eventClick: function(info) {
            window.location.href = '/admin/agendamentos/editar?id=' + info.event.id;
        }
    });
    
    calendar.render();
});
</script>

<?php require 'views/admin/includes/footer.php'; ?>
