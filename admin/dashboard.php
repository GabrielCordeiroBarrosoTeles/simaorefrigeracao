<?php
require_once '../bootstrap.php';
require_once '../includes/template_helper.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('../admin-login.php');
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
    'contatos_novos' => countContatosNovos($db),
    'agendamentos_pendentes' => countAgendamentosPorStatus($db, 'pendente'),
    'agendamentos_concluidos' => countAgendamentosPorStatus($db, 'concluido'),
    'agendamentos_cancelados' => countAgendamentosPorStatus($db, 'cancelado')
];

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

function countAgendamentosPorStatus($db, $status) {
    $query = "SELECT COUNT(*) as total FROM agendamentos WHERE status = :status";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
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

// Renderizar a view
$page_title = 'Dashboard';
$data = [
    'page_title' => $page_title,
    'stats' => $stats,
    'agendamentos_recentes' => $agendamentos_recentes,
    'proximos_agendamentos' => $proximos_agendamentos,
    'contatos_recentes' => $contatos_recentes
];

render_view('views/admin/dashboard_content.php', $data);
