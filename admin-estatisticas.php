<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
   redirect('admin-login.php');
}

// Conexão com o banco de dados
$db = db_connect();

// Obter estatísticas gerais
$stats = [
  'clientes' => 0,
  'agendamentos' => 0,
  'tecnicos' => 0,
  'servicos' => 0,
  'contatos' => 0,
  'depoimentos' => 0,
  'agendamentos_pendentes' => 0,
  'agendamentos_concluidos' => 0,
  'agendamentos_cancelados' => 0
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
  
  // Contar contatos
  $query = "SELECT COUNT(*) as total FROM contatos";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $stats['contatos'] = $result['total'] ?? 0;
  
  // Contar depoimentos
  $query = "SELECT COUNT(*) as total FROM depoimentos";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $stats['depoimentos'] = $result['total'] ?? 0;
  
  // Contar agendamentos por status
  $query = "SELECT status, COUNT(*) as total FROM agendamentos GROUP BY status";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $status_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  foreach ($status_results as $status) {
      if ($status['status'] === 'pendente') {
          $stats['agendamentos_pendentes'] = $status['total'];
      } elseif ($status['status'] === 'concluido') {
          $stats['agendamentos_concluidos'] = $status['total'];
      } elseif ($status['status'] === 'cancelado') {
          $stats['agendamentos_cancelados'] = $status['total'];
      }
  }
} catch (PDOException $e) {
  // Em caso de erro, manter os valores padrão
  error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// Obter estatísticas de agendamentos por mês (últimos 6 meses)
$agendamentos_por_mes = [];
try {
  $query = "SELECT 
              DATE_FORMAT(data_agendamento, '%Y-%m') as mes,
              COUNT(*) as total
            FROM agendamentos
            WHERE data_agendamento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(data_agendamento, '%Y-%m')
            ORDER BY mes ASC";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $agendamentos_por_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Se não houver dados, criar dados de exemplo para evitar gráficos em branco
  if (empty($agendamentos_por_mes)) {
      for ($i = 5; $i >= 0; $i--) {
          $month = date('Y-m', strtotime("-$i months"));
          $agendamentos_por_mes[] = [
              'mes' => $month,
              'total' => 0
          ];
      }
  }
} catch (PDOException $e) {
  error_log("Erro ao buscar agendamentos por mês: " . $e->getMessage());
  // Em caso de erro, criar dados de exemplo
  for ($i = 5; $i >= 0; $i--) {
      $month = date('Y-m', strtotime("-$i months"));
      $agendamentos_por_mes[] = [
          'mes' => $month,
          'total' => 0
      ];
  }
}

// Obter estatísticas de serviços mais solicitados
$servicos_populares = [];
try {
  $query = "SELECT 
          s.titulo as servico,
          COUNT(a.id) as total
        FROM agendamentos a
        JOIN servicos s ON a.servico_id = s.id
        GROUP BY a.servico_id, s.titulo
        ORDER BY total DESC
        LIMIT 5";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $servicos_populares = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Se não houver dados, criar dados de exemplo
  if (empty($servicos_populares)) {
      $servicos_populares = [
          ['servico' => 'Instalação', 'total' => 0],
          ['servico' => 'Manutenção', 'total' => 0],
          ['servico' => 'Limpeza', 'total' => 0],
          ['servico' => 'Reparo', 'total' => 0],
          ['servico' => 'Diagnóstico', 'total' => 0]
      ];
  }
} catch (PDOException $e) {
  error_log("Erro ao buscar serviços populares: " . $e->getMessage());
  // Em caso de erro, criar dados de exemplo
  $servicos_populares = [
      ['servico' => 'Instalação', 'total' => 0],
      ['servico' => 'Manutenção', 'total' => 0],
      ['servico' => 'Limpeza', 'total' => 0],
      ['servico' => 'Reparo', 'total' => 0],
      ['servico' => 'Diagnóstico', 'total' => 0]
  ];
}

// Obter estatísticas de agendamentos por técnico
$agendamentos_por_tecnico = [];
try {
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
  $agendamentos_por_tecnico = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Se não houver dados, criar dados de exemplo
  if (empty($agendamentos_por_tecnico)) {
      $agendamentos_por_tecnico = [
          ['tecnico' => 'Técnico 1', 'total' => 0],
          ['tecnico' => 'Técnico 2', 'total' => 0],
          ['tecnico' => 'Técnico 3', 'total' => 0],
          ['tecnico' => 'Técnico 4', 'total' => 0],
          ['tecnico' => 'Técnico 5', 'total' => 0]
      ];
  }
} catch (PDOException $e) {
  error_log("Erro ao buscar agendamentos por técnico: " . $e->getMessage());
  // Em caso de erro, criar dados de exemplo
  $agendamentos_por_tecnico = [
      ['tecnico' => 'Técnico 1', 'total' => 0],
      ['tecnico' => 'Técnico 2', 'total' => 0],
      ['tecnico' => 'Técnico 3', 'total' => 0],
      ['tecnico' => 'Técnico 4', 'total' => 0],
      ['tecnico' => 'Técnico 5', 'total' => 0]
  ];
}

// Formatar dados para os gráficos
$meses_labels = [];
$meses_dados = [];

foreach ($agendamentos_por_mes as $item) {
  $data = DateTime::createFromFormat('Y-m', $item['mes']);
  $meses_labels[] = $data->format('M/Y');
  $meses_dados[] = $item['total'];
}

$servicos_labels = [];
$servicos_dados = [];

foreach ($servicos_populares as $item) {
  $servicos_labels[] = $item['servico'];
  $servicos_dados[] = $item['total'];
}

$tecnicos_labels = [];
$tecnicos_dados = [];

foreach ($agendamentos_por_tecnico as $item) {
  $tecnicos_labels[] = $item['tecnico'];
  $tecnicos_dados[] = $item['total'];
}

$status_labels = ['Pendentes', 'Concluídos', 'Cancelados'];
$status_dados = [
  $stats['agendamentos_pendentes'],
  $stats['agendamentos_concluidos'],
  $stats['agendamentos_cancelados']
];
$status_cores = ['#f6c23e', '#1cc88a', '#e74a3b'];

// Título da página
$page_title = 'Estatísticas';

// Incluir o cabeçalho
include 'views/admin/includes/header.php';
?>

<!-- Sidebar -->
<?php include 'views/admin/includes/sidebar.php'; ?>

<div class="container-fluid">
   <div class="d-sm-flex align-items-center justify-content-between mb-4">
       <h1 class="h3 mb-0 text-gray-800">Estatísticas</h1>
       <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="printReport">
           <i class="fas fa-download fa-sm text-white-50"></i> Gerar Relatório
       </a>
   </div>
   
   <?php display_flash_message(); ?>
   
   <!-- Cards de Estatísticas -->
   <div class="row">
       <div class="col-xl-3 col-md-6 mb-4">
           <div class="card border-left-primary shadow h-100 py-2">
               <div class="card-body">
                   <div class="row no-gutters align-items-center">
                       <div class="col mr-2">
                           <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                               Clientes</div>
                           <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['clientes'] ?></div>
                       </div>
                       <div class="col-auto">
                           <i class="fas fa-users fa-2x text-gray-300"></i>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="col-xl-3 col-md-6 mb-4">
           <div class="card border-left-success shadow h-100 py-2">
               <div class="card-body">
                   <div class="row no-gutters align-items-center">
                       <div class="col mr-2">
                           <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                               Agendamentos</div>
                           <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['agendamentos'] ?></div>
                       </div>
                       <div class="col-auto">
                           <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="col-xl-3 col-md-6 mb-4">
           <div class="card border-left-info shadow h-100 py-2">
               <div class="card-body">
                   <div class="row no-gutters align-items-center">
                       <div class="col mr-2">
                           <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                               Técnicos</div>
                           <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['tecnicos'] ?></div>
                       </div>
                       <div class="col-auto">
                           <i class="fas fa-user-cog fa-2x text-gray-300"></i>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="col-xl-3 col-md-6 mb-4">
           <div class="card border-left-warning shadow h-100 py-2">
               <div class="card-body">
                   <div class="row no-gutters align-items-center">
                       <div class="col mr-2">
                           <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                               Serviços</div>
                           <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['servicos'] ?></div>
                       </div>
                       <div class="col-auto">
                           <i class="fas fa-tools fa-2x text-gray-300"></i>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <!-- Gráficos -->
   <div class="row">
       <!-- Gráfico de Agendamentos por Mês -->
       <div class="col-xl-8 col-lg-7">
           <div class="card shadow mb-4">
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                   <h6 class="m-0 font-weight-bold text-primary">Agendamentos por Mês</h6>
               </div>
               <div class="card-body">
                   <div class="chart-area">
                       <canvas id="agendamentosPorMes"></canvas>
                   </div>
               </div>
           </div>
       </div>
       
       <!-- Gráfico de Status dos Agendamentos -->
       <div class="col-xl-4 col-lg-5">
           <div class="card shadow mb-4">
               <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                   <h6 class="m-0 font-weight-bold text-primary">Status dos Agendamentos</h6>
               </div>
               <div class="card-body">
                   <div class="chart-pie pt-4 pb-2">
                       <canvas id="statusAgendamentos"></canvas>
                   </div>
                   <div class="mt-4 text-center small">
                       <?php foreach ($status_labels as $index => $label): ?>
                           <span class="mr-2">
                               <i class="fas fa-circle" style="color: <?= $status_cores[$index] ?>"></i> <?= $label ?>
                           </span>
                       <?php endforeach; ?>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <!-- Serviços Mais Populares e Agendamentos por Técnico -->
   <div class="row">
       <div class="col-lg-6 mb-4">
           <div class="card shadow mb-4">
               <div class="card-header py-3">
                   <h6 class="m-0 font-weight-bold text-primary">Serviços Mais Solicitados</h6>
               </div>
               <div class="card-body">
                   <div class="chart-bar">
                       <canvas id="servicosPopulares"></canvas>
                   </div>
               </div>
           </div>
       </div>
       
       <div class="col-lg-6 mb-4">
           <div class="card shadow mb-4">
               <div class="card-header py-3">
                   <h6 class="m-0 font-weight-bold text-primary">Agendamentos por Técnico</h6>
               </div>
               <div class="card-body">
                   <div class="chart-bar">
                       <canvas id="tecnicosChart"></canvas>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <!-- Estatísticas Adicionais -->
   <div class="row">
       <div class="col-lg-12 mb-4">
           <div class="card shadow mb-4">
               <div class="card-header py-3">
                   <h6 class="m-0 font-weight-bold text-primary">Estatísticas Adicionais</h6>
               </div>
               <div class="card-body">
                   <div class="row">
                       <div class="col-md-6 mb-4">
                           <div class="card bg-light text-dark h-100 py-2">
                               <div class="card-body">
                                   <div class="row no-gutters align-items-center">
                                       <div class="col mr-2">
                                           <div class="text-xs font-weight-bold text-uppercase mb-1">
                                               Contatos Recebidos</div>
                                           <div class="h5 mb-0 font-weight-bold"><?= $stats['contatos'] ?></div>
                                       </div>
                                       <div class="col-auto">
                                           <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </div>
                       
                       <div class="col-md-6 mb-4">
                           <div class="card bg-light text-dark h-100 py-2">
                               <div class="card-body">
                                   <div class="row no-gutters align-items-center">
                                       <div class="col mr-2">
                                           <div class="text-xs font-weight-bold text-uppercase mb-1">
                                               Depoimentos</div>
                                           <div class="h5 mb-0 font-weight-bold"><?= $stats['depoimentos'] ?></div>
                                       </div>
                                       <div class="col-auto">
                                           <i class="fas fa-comments fa-2x text-gray-300"></i>
                                       </div>
                                   </div>
                               </div>
                           </div>
                       </div>
                   </div>
                   
                   <div class="text-center mt-3">
                       <a href="admin-contatos.php" class="btn btn-sm btn-primary mr-2">
                           <i class="fas fa-envelope mr-1"></i> Ver Contatos
                       </a>
                       <a href="admin-depoimentos.php" class="btn btn-sm btn-primary">
                           <i class="fas fa-comments mr-1"></i> Ver Depoimentos
                       </a>
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
   var statusCtx = document.getElementById('statusAgendamentos').getContext('2d');
   var statusChart = new Chart(statusCtx, {
       type: 'pie',
       data: {
           labels: <?= json_encode($status_labels) ?>,
           datasets: [{
               data: <?= json_encode($status_dados) ?>,
               backgroundColor: <?= json_encode($status_cores) ?>,
               hoverBackgroundColor: <?= json_encode($status_cores) ?>,
               hoverBorderColor: "rgba(234, 236, 244, 1)",
           }],
       },
       options: {
           maintainAspectRatio: false,
           plugins: {
               legend: {
                   display: false
               },
               tooltip: {
                   backgroundColor: "rgb(255,255,255)",
                   bodyColor: "#858796",
                   borderColor: '#dddfeb',
                   borderWidth: 1,
                   xPadding: 15,
                   yPadding: 15,
                   displayColors: false,
                   caretPadding: 10,
               }
           },
           cutout: '70%',
       },
   });
   
   // Agendamentos por Mês
   var monthlyCtx = document.getElementById('agendamentosPorMes').getContext('2d');
   var monthlyChart = new Chart(monthlyCtx, {
       type: 'line',
       data: {
           labels: <?= json_encode($meses_labels) ?>,
           datasets: [{
               label: "Agendamentos",
               lineTension: 0.3,
               backgroundColor: "rgba(78, 115, 223, 0.05)",
               borderColor: "rgba(78, 115, 223, 1)",
               pointRadius: 3,
               pointBackgroundColor: "rgba(78, 115, 223, 1)",
               pointBorderColor: "rgba(78, 115, 223, 1)",
               pointHoverRadius: 3,
               pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
               pointHoverBorderColor: "rgba(78, 115, 223, 1)",
               pointHitRadius: 10,
               pointBorderWidth: 2,
               data: <?= json_encode($meses_dados) ?>,
           }],
       },
       options: {
           maintainAspectRatio: false,
           layout: {
               padding: {
                   left: 10,
                   right: 25,
                   top: 25,
                   bottom: 0
               }
           },
           scales: {
               x: {
                   grid: {
                       display: false,
                       drawBorder: false
                   },
                   ticks: {
                       maxTicksLimit: 7
                   }
               },
               y: {
                   ticks: {
                       maxTicksLimit: 5,
                       padding: 10,
                       beginAtZero: true
                   },
                   grid: {
                       color: "rgb(234, 236, 244)",
                       zeroLineColor: "rgb(234, 236, 244)",
                       drawBorder: false,
                       borderDash: [2],
                       zeroLineBorderDash: [2]
                   }
               },
           },
           plugins: {
               legend: {
                   display: false
               },
               tooltip: {
                   backgroundColor: "rgb(255,255,255)",
                   bodyColor: "#858796",
                   titleMarginBottom: 10,
                   titleColor: '#6e707e',
                   titleFontSize: 14,
                   borderColor: '#dddfeb',
                   borderWidth: 1,
                   xPadding: 15,
                   yPadding: 15,
                   displayColors: false,
                   intersect: false,
                   mode: 'index',
                   caretPadding: 10,
               }
           }
       }
   });
   
   // Serviços Mais Populares
   var servicosCtx = document.getElementById('servicosPopulares').getContext('2d');
   var servicosChart = new Chart(servicosCtx, {
       type: 'bar',
       data: {
           labels: <?= json_encode($servicos_labels) ?>,
           datasets: [{
               label: "Agendamentos",
               backgroundColor: "#4e73df",
               hoverBackgroundColor: "#2e59d9",
               borderColor: "#4e73df",
               data: <?= json_encode($servicos_dados) ?>,
           }],
       },
       options: {
           maintainAspectRatio: false,
           layout: {
               padding: {
                   left: 10,
                   right: 25,
                   top: 25,
                   bottom: 0
               }
           },
           scales: {
               x: {
                   grid: {
                       display: false,
                       drawBorder: false
                   },
               },
               y: {
                   ticks: {
                       beginAtZero: true
                   },
                   grid: {
                       color: "rgb(234, 236, 244)",
                       zeroLineColor: "rgb(234, 236, 244)",
                       drawBorder: false,
                       borderDash: [2],
                       zeroLineBorderDash: [2]
                   }
               },
           },
           plugins: {
               legend: {
                   display: false
               },
               tooltip: {
                   backgroundColor: "rgb(255,255,255)",
                   bodyColor: "#858796",
                   titleMarginBottom: 10,
                   titleColor: '#6e707e',
                   titleFontSize: 14,
                   borderColor: '#dddfeb',
                   borderWidth: 1,
                   xPadding: 15,
                   yPadding: 15,
                   displayColors: false,
                   caretPadding: 10,
               }
           }
       }
   });
   
   // Agendamentos por Técnico
   var tecnicosCtx = document.getElementById('tecnicosChart').getContext('2d');
   var tecnicosChart = new Chart(tecnicosCtx, {
       type: 'bar',
       data: {
           labels: <?= json_encode($tecnicos_labels) ?>,
           datasets: [{
               label: "Agendamentos",
               backgroundColor: "#36b9cc",
               hoverBackgroundColor: "#2c9faf",
               borderColor: "#36b9cc",
               data: <?= json_encode($tecnicos_dados) ?>,
           }],
       },
       options: {
           maintainAspectRatio: false,
           layout: {
               padding: {
                   left: 10,
                   right: 25,
                   top: 25,
                   bottom: 0
               }
           },
           scales: {
               x: {
                   grid: {
                       display: false,
                       drawBorder: false
                   },
               },
               y: {
                   ticks: {
                       beginAtZero: true
                   },
                   grid: {
                       color: "rgb(234, 236, 244)",
                       zeroLineColor: "rgb(234, 236, 244)",
                       drawBorder: false,
                       borderDash: [2],
                       zeroLineBorderDash: [2]
                   }
               },
           },
           plugins: {
               legend: {
                   display: false
               },
               tooltip: {
                   backgroundColor: "rgb(255,255,255)",
                   bodyColor: "#858796",
                   titleMarginBottom: 10,
                   titleColor: '#6e707e',
                   titleFontSize: 14,
                   borderColor: '#dddfeb',
                   borderWidth: 1,
                   xPadding: 15,
                   yPadding: 15,
                   displayColors: false,
                   caretPadding: 10,
               }
           }
       }
   });
   
   // Imprimir relatório
   $("#printReport").click(function() {
       window.print();
   });
});
</script>

<?php include 'views/admin/includes/footer.php'; ?>
