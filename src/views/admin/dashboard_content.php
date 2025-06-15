<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
</div>

<!-- Cards de Estatísticas -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $stats['clientes'] ?></h3>
                <p>CLIENTES</p>
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
                <p>AGENDAMENTOS</p>
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
                <p>TÉCNICOS</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-hard-hat"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3><?= $stats['servicos'] ?></h3>
                <p>SERVIÇOS</p>
            </div>
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>
        </div>
    </div>
</div>

<!-- Status dos Agendamentos e Agendamentos por Técnico -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Status dos Agendamentos</h3>
            </div>
            <div class="card-body">
                <canvas id="statusAgendamentos" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Agendamentos por Técnico</h3>
            </div>
            <div class="card-body">
                <canvas id="tecnicosChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Agendamentos Recentes e Próximos -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Agendamentos Recentes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($agendamentos_recentes)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum agendamento encontrado.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($agendamentos_recentes as $agendamento): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($agendamento['data_agendamento'])) ?></td>
                                <td><?= $agendamento['cliente_nome'] ?></td>
                                <td><?= $agendamento['servico_nome'] ?></td>
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
                                    <span class="badge bg-<?= $status_class ?>">
                                        <?= ucfirst($agendamento['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Próximos Agendamentos</h3>
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
                        <?php if (empty($proximos_agendamentos)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum agendamento futuro.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($proximos_agendamentos as $agendamento): ?>
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
                <a href="agendamentos.php" class="btn btn-primary">Ver Todos</a>
            </div>
        </div>
    </div>
</div>

<!-- Extra JS para os gráficos -->
<?php ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status dos Agendamentos
    var statusCtx = document.getElementById('statusAgendamentos').getContext('2d');
    var statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pendentes', 'Concluídos', 'Cancelados'],
            datasets: [{
                data: [
                    <?= $stats['agendamentos_pendentes'] ?? 0 ?>, 
                    <?= $stats['agendamentos_concluidos'] ?? 0 ?>, 
                    <?= $stats['agendamentos_cancelados'] ?? 0 ?>
                ],
                backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                hoverBackgroundColor: ['#e0a800', '#218838', '#c82333'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '70%'
        }
    });
    
    // Agendamentos por Técnico (dados de exemplo)
    var tecnicosCtx = document.getElementById('tecnicosChart').getContext('2d');
    var tecnicosChart = new Chart(tecnicosCtx, {
        type: 'bar',
        data: {
            labels: ['Técnico 1', 'Técnico 2', 'Técnico 3', 'Técnico 4'],
            datasets: [{
                label: 'Agendamentos',
                data: [12, 8, 15, 6],
                backgroundColor: '#17a2b8',
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
<?php $extra_js = ob_get_clean(); ?>
