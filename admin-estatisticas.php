<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin/login');
}

// Conexão com o banco de dados
$db = db_connect();

// Obter estatísticas
$stats = [
    'clientes' => 0,
    'agendamentos' => 0,
    'tecnicos' => 0,
    'servicos' => 0,
    'agendamentos_pendentes' => 0,
    'agendamentos_concluidos' => 0,
    'agendamentos_cancelados' => 0,
    'faturamento_total' => 0,
    'faturamento_pendente' => 0
];

try {
    // Contar clientes
    $query = "SELECT COUNT(*) as total FROM clientes";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['clientes'] = $result['total'] ?? 0;
    
    // Contar agendamentos
    $query = "SELECT COUNT(*) as total FROM agendamentos";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['agendamentos'] = $result['total'] ?? 0;
    
    // Contar técnicos
    $query = "SELECT COUNT(*) as total FROM tecnicos";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['tecnicos'] = $result['total'] ?? 0;
    
    // Contar serviços
    $query = "SELECT COUNT(*) as total FROM servicos";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['servicos'] = $result['total'] ?? 0;
    
    // Contar agendamentos por status
    $query = "SELECT status, COUNT(*) as total FROM agendamentos GROUP BY status";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($status_counts as $status) {
        if ($status['status'] === 'pendente') {
            $stats['agendamentos_pendentes'] = $status['total'];
        } elseif ($status['status'] === 'concluido') {
            $stats['agendamentos_concluidos'] = $status['total'];
        } elseif ($status['status'] === 'cancelado') {
            $stats['agendamentos_cancelados'] = $status['total'];
        }
    }
    
    // Calcular faturamento total e pendente
    $query = "SELECT SUM(valor) as total, SUM(valor_pendente) as pendente FROM agendamentos";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['faturamento_total'] = $result['total'] ?? 0;
    $stats['faturamento_pendente'] = $result['pendente'] ?? 0;
    
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar estatísticas.');
    if (DEBUG_MODE) {
        $_SESSION['error_details'] = $e->getMessage();
    }
}

// Obter dados para gráficos
$chart_data = [
    'agendamentos_por_mes' => [],
    'agendamentos_por_tecnico' => [],
    'servicos_mais_solicitados' => []
];

try {
    // Agendamentos por mês (últimos 6 meses)
    $query = "SELECT 
                DATE_FORMAT(data_agendamento, '%Y-%m') as mes, 
                COUNT(*) as total 
              FROM agendamentos 
              WHERE data_agendamento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
              GROUP BY mes 
              ORDER BY mes ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $chart_data['agendamentos_por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agendamentos por técnico
    $query = "SELECT 
                t.nome as tecnico, 
                COUNT(a.id) as total 
              FROM agendamentos a
              JOIN tecnicos t ON a.tecnico_id = t.id
              GROUP BY a.tecnico_id 
              ORDER BY total DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $chart_data['agendamentos_por_tecnico'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Serviços mais solicitados
    $query = "SELECT 
                s.nome as servico, 
                COUNT(a.id) as total 
              FROM agendamentos a
              JOIN servicos s ON a.servico_id = s.id
              GROUP BY a.servico_id 
              ORDER BY total DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $chart_data['servicos_mais_solicitados'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao buscar dados para gráficos.');
    if (DEBUG_MODE) {
        $_SESSION['error_details'] = $e->getMessage();
    }
}

// Incluir cabeçalho
include 'views/admin/includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Estatísticas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="admin-dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Estatísticas</li>
    </ol>
    
    <?php display_flash_message(); ?>
    
    <!-- Cards de Estatísticas -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total de Clientes</div>
                            <div class="display-6"><?= number_format($stats['clientes']) ?></div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="admin-table.php?table=clientes">Ver Detalhes</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total de Agendamentos</div>
                            <div class="display-6"><?= number_format($stats['agendamentos']) ?></div>
                        </div>
                        <div>
                            <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="admin-table.php?table=agendamentos">Ver Detalhes</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total de Técnicos</div>
                            <div class="display-6"><?= number_format($stats['tecnicos']) ?></div>
                        </div>
                        <div>
                            <i class="fas fa-user-hard-hat fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="admin-table.php?table=tecnicos">Ver Detalhes</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total de Serviços</div>
                            <div class="display-6"><?= number_format($stats['servicos']) ?></div>
                        </div>
                        <div>
                            <i class="fas fa-tools fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="admin-table.php?table=servicos">Ver Detalhes</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos e Estatísticas Detalhadas -->
    <div class="row">
        <!-- Status dos Agendamentos -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Status dos Agendamentos
                </div>
                <div class="card-body">
                    <canvas id="statusChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Agendamentos por Mês -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Agendamentos por Mês
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Agendamentos por Técnico -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Agendamentos por Técnico
                </div>
                <div class="card-body">
                    <canvas id="technicianChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Serviços Mais Solicitados -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Serviços Mais Solicitados
                </div>
                <div class="card-body">
                    <canvas id="servicesChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Faturamento -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-dollar-sign me-1"></i>
                    Faturamento
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Faturamento Total</h5>
                                    <h2 class="display-4">R$ <?= number_format($stats['faturamento_total'], 2, ',', '.') ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-danger text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Valores Pendentes</h5>
                                    <h2 class="display-4">R$ <?= number_format($stats['faturamento_pendente'], 2, ',', '.') ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status dos Agendamentos
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Pendentes', 'Concluídos', 'Cancelados'],
            datasets: [{
                data: [
                    <?= $stats['agendamentos_pendentes'] ?>,
                    <?= $stats['agendamentos_concluidos'] ?>,
                    <?= $stats['agendamentos_cancelados'] ?>
                ],
                backgroundColor: [
                    '#ffc107',
                    '#28a745',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Agendamentos por Mês
    var monthlyData = <?= json_encode($chart_data['agendamentos_por_mes']) ?>;
    var monthlyLabels = monthlyData.map(function(item) {  ?>;
    var monthlyLabels = monthlyData.map(function(item) {
        var date = new Date(item.mes + '-01');
        return date.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
    });
    var monthlyValues = monthlyData.map(function(item) {
        return item.total;
    });
    
    var monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    var monthlyChart = new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Agendamentos',
                data: monthlyValues,
                backgroundColor: '#4e73df'
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
            }
        }
    });
    
    // Agendamentos por Técnico
    var technicianData = <?= json_encode($chart_data['agendamentos_por_tecnico']) ?>;
    var technicianLabels = technicianData.map(function(item) {
        return item.tecnico;
    });
    var technicianValues = technicianData.map(function(item) {
        return item.total;
    });
    
    var technicianCtx = document.getElementById('technicianChart').getContext('2d');
    var technicianChart = new Chart(technicianCtx, {
        type: 'bar',
        data: {
            labels: technicianLabels,
            datasets: [{
                label: 'Agendamentos',
                data: technicianValues,
                backgroundColor: '#36b9cc'
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
            }
        }
    });
    
    // Serviços Mais Solicitados
    var servicesData = <?= json_encode($chart_data['servicos_mais_solicitados']) ?>;
    var servicesLabels = servicesData.map(function(item) {
        return item.servico;
    });
    var servicesValues = servicesData.map(function(item) {
        return item.total;
    });
    
    var servicesCtx = document.getElementById('servicesChart').getContext('2d');
    var servicesChart = new Chart(servicesCtx, {
        type: 'bar',
        data: {
            labels: servicesLabels,
            datasets: [{
                label: 'Agendamentos',
                data: servicesValues,
                backgroundColor: '#1cc88a'
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
            }
        }
    });
});
</script>

<?php include 'views/admin/includes/footer.php'; ?>
