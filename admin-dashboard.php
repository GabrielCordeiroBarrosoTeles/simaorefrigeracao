<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
   redirect('admin-login.php');
}

// Conexão com o banco de dados
$db = db_connect();

// Estatísticas para o dashboard
$stats = [
   'clientes' => countRows($db, 'clientes'),
   'servicos' => countRows($db, 'servicos'),
   'agendamentos' => countRows($db, 'agendamentos'),
   'tecnicos' => countRows($db, 'tecnicos'),
   'agendamentos_hoje' => countAgendamentosHoje($db),
   'agendamentos_semana' => countAgendamentosSemana($db),
   'contatos_novos' => countContatosNovos($db)
];

// Função para contar agendamentos por status
function countAgendamentosPorStatus($db, $status) {
   $query = "SELECT COUNT(*) as total FROM agendamentos WHERE status = :status";
   $stmt = $db->prepare($query);
   $stmt->bindParam(':status', $status);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result['total'] ?? 0;
}

// Agendamentos recentes
$agendamentos_recentes = getAgendamentosRecentes($db);

// Próximos agendamentos
$proximos_agendamentos = getProximosAgendamentos($db);

// Contatos recentes
$contatos_recentes = getContatosRecentes($db);

// Funções auxiliares
function countRows($db, $table) {
   $query = "SELECT COUNT(*) as total FROM $table";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result['total'] ?? 0;
}

function countAgendamentosHoje($db) {
   $query = "SELECT COUNT(*) as total FROM agendamentos WHERE DATE(data_agendamento) = CURDATE()";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result['total'] ?? 0;
}

function countAgendamentosSemana($db) {
   $query = "SELECT COUNT(*) as total FROM agendamentos 
             WHERE YEARWEEK(data_agendamento, 1) = YEARWEEK(CURDATE(), 1)";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result['total'] ?? 0;
}

function countContatosNovos($db) {
   $query = "SELECT COUNT(*) as total FROM contatos WHERE status = 'novo'";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   return $result['total'] ?? 0;
}

function getAgendamentosRecentes($db) {
   $query = "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome, t.nome as tecnico_nome
             FROM agendamentos a
             LEFT JOIN clientes c ON a.cliente_id = c.id
             LEFT JOIN servicos s ON a.servico_id = s.id
             LEFT JOIN tecnicos t ON a.tecnico_id = t.id
             ORDER BY a.data_agendamento DESC
             LIMIT 5";
   $stmt = $db->prepare($query);
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProximosAgendamentos($db) {
   $query = "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome, t.nome as tecnico_nome
             FROM agendamentos a
             LEFT JOIN clientes c ON a.cliente_id = c.id
             LEFT JOIN servicos s ON a.servico_id = s.id
             LEFT JOIN tecnicos t ON a.tecnico_id = t.id
             WHERE a.data_agendamento >= CURDATE()
             ORDER BY a.data_agendamento ASC
             LIMIT 5";
   $stmt = $db->prepare($query);
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getContatosRecentes($db) {
   $query = "SELECT * FROM contatos ORDER BY data_criacao DESC LIMIT 5";
   $stmt = $db->prepare($query);
   $stmt->execute();
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Título da página
$page_title = 'Dashboard';

// Incluir o cabeçalho
include 'views/admin/includes/header.php';
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="gerar-pdf.php?tipo=relatorio&periodo=mensal" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="btnGerarRelatorio">
            <i class="fas fa-download fa-sm text-white-50"></i> Gerar Relatório
        </a>
    </div>

    <!-- Cards de Estatísticas (Melhorados) -->
    <div class="row">
        <!-- Card Clientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Clientes</div>
                            <div class="h1 mb-0 font-weight-bold text-gray-800"><?= $stats['clientes'] ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-users fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="admin-table.php?table=clientes" class="text-primary small stretched-link">Ver detalhes <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Card Agendamentos -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Agendamentos</div>
                            <div class="h1 mb-0 font-weight-bold text-gray-800"><?= $stats['agendamentos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success">
                                <i class="fas fa-calendar-check fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="admin-table.php?table=agendamentos" class="text-success small stretched-link">Ver detalhes <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Card Técnicos (Com ícone adicionado) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <i class="fas fa-user-hard-hat mr-1"></i> Técnicos</div>
                            <div class="h1 mb-0 font-weight-bold text-gray-800"><?= $stats['tecnicos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info">
                                <i class="fas fa-user-hard-hat fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="admin-table.php?table=tecnicos" class="text-info small stretched-link">Ver detalhes <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Card Serviços -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Serviços</div>
                            <div class="h1 mb-0 font-weight-bold text-gray-800"><?= $stats['servicos'] ?></div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-tools fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="admin-table.php?table=servicos" class="text-warning small stretched-link">Ver detalhes <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status dos Agendamentos e Agendamentos por Técnico -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Status dos Agendamentos</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusAgendamentos" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Agendamentos por Técnico</h6>
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
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Agendamentos Recentes</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
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
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Próximos Agendamentos</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
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
                </div>
                <div class="card-footer text-center">
                    <a href="admin-table.php?table=agendamentos" class="btn btn-primary">Ver Todos</a>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- Chart.js -->
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
                   <?= countAgendamentosPorStatus($db, 'pendente') ?>, 
                   <?= countAgendamentosPorStatus($db, 'concluido') ?>, 
                   <?= countAgendamentosPorStatus($db, 'cancelado') ?>
               ],
               backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b'],
               hoverBackgroundColor: ['#e0a800', '#169b6b', '#d52a1a'],
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
   
   // Agendamentos por Técnico (dados reais do banco)
   var tecnicosCtx = document.getElementById('tecnicosChart').getContext('2d');
   var tecnicosChart = new Chart(tecnicosCtx, {
       type: 'bar',
       data: {
           labels: [
               <?php
               // Buscar dados reais de técnicos e seus agendamentos
               $query = "SELECT t.nome, COUNT(a.id) as total 
                        FROM tecnicos t 
                        LEFT JOIN agendamentos a ON t.id = a.tecnico_id 
                        GROUP BY t.id 
                        ORDER BY total DESC 
                        LIMIT 5";
               $stmt = $db->prepare($query);
               $stmt->execute();
               $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
               
               $nomes = [];
               $totais = [];
               
               foreach ($tecnicos as $tecnico) {
                   $nomes[] = "'" . addslashes($tecnico['nome']) . "'";
                   $totais[] = $tecnico['total'];
               }
               
               echo implode(', ', $nomes);
               ?>
           ],
           datasets: [{
               label: 'Agendamentos',
               data: [<?= implode(', ', $totais) ?>],
               backgroundColor: '#36b9cc',
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

<!-- Arquivo para gerar relatório -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botão de gerar relatório
    const btnRelatorio = document.getElementById('btnGerarRelatorio');
    if (btnRelatorio) {
        btnRelatorio.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Mostrar modal de carregamento
            $('#loadingModal').modal('show');
            
            // Simular geração de relatório
            setTimeout(function() {
                $('#loadingModal').modal('hide');
                
                // Redirecionar para o relatório ou fazer download
                window.location.href = btnRelatorio.getAttribute('href');
            }, 1500);
        });
    }
    
    // Garantir que o botão de logout funcione corretamente
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', function(e) {
            // Não previne o comportamento padrão para permitir a navegação normal
            console.log('Logout iniciado');
        });
    }
});
</script>

<!-- Modal de Carregamento -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
                <h5>Gerando relatório...</h5>
                <p class="mb-0">Por favor, aguarde enquanto preparamos seu relatório.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'views/admin/includes/footer.php'; ?>
