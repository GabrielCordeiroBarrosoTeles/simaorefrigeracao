<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Conectar ao banco de dados
$db = db_connect();

// Obter filtros
$tecnico_id = isset($_GET['tecnico_id']) && !empty($_GET['tecnico_id']) ? intval($_GET['tecnico_id']) : null;
$servico_id = isset($_GET['servico_id']) && !empty($_GET['servico_id']) ? intval($_GET['servico_id']) : null;
$status = isset($_GET['status']) && !empty($_GET['status']) ? sanitize($_GET['status']) : null;

// Construir consulta SQL com filtros
$sql = "SELECT a.*, c.nome as cliente_nome, s.titulo as servico_nome, t.nome as tecnico_nome, t.cor as tecnico_cor 
        FROM agendamentos a 
        LEFT JOIN clientes c ON a.cliente_id = c.id 
        LEFT JOIN servicos s ON a.servico_id = s.id 
        LEFT JOIN tecnicos t ON a.tecnico_id = t.id 
        WHERE 1=1";

$params = [];

if ($tecnico_id) {
    $sql .= " AND a.tecnico_id = :tecnico_id";
    $params[':tecnico_id'] = $tecnico_id;
}

if ($servico_id) {
    $sql .= " AND a.servico_id = :servico_id";
    $params[':servico_id'] = $servico_id;
}

if ($status) {
    $sql .= " AND a.status = :status";
    $params[':status'] = $status;
}

$sql .= " ORDER BY a.data_agendamento, a.hora_inicio";

// Executar consulta
try {
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao buscar agendamentos: ' . $e->getMessage()]);
    exit;
}

// Formatar dados para o FullCalendar
$events = [];
foreach ($agendamentos as $agendamento) {
    // Definir cor com base no status
    $backgroundColor = $agendamento['tecnico_cor'] ?? '#3b82f6';
    $borderColor = $backgroundColor;
    $textColor = '#ffffff';
    
    if ($agendamento['status'] === 'cancelado') {
        $backgroundColor = '#ef4444';
        $borderColor = '#ef4444';
    } elseif ($agendamento['status'] === 'concluido') {
        $backgroundColor = '#10b981';
        $borderColor = '#10b981';
    }
    
    // Definir horário de início e fim
    $start = $agendamento['data_agendamento'] . 'T' . $agendamento['hora_inicio'];
    $end = $agendamento['data_agendamento'] . 'T' . ($agendamento['hora_fim'] ?? '23:59:59');
    
    // Criar evento
    $events[] = [
        'id' => $agendamento['id'],
        'title' => $agendamento['titulo'],
        'start' => $start,
        'end' => $end,
        'backgroundColor' => $backgroundColor,
        'borderColor' => $borderColor,
        'textColor' => $textColor,
        'url' => 'admin-form.php?form=agendamento&id=' . $agendamento['id'],
        'extendedProps' => [
            'cliente' => $agendamento['cliente_nome'],
            'servico' => $agendamento['servico_nome'],
            'tecnico' => $agendamento['tecnico_nome'],
            'status' => $agendamento['status']
        ],
        'description' => 'Cliente: ' . $agendamento['cliente_nome'] . '<br>' .
                         'Serviço: ' . $agendamento['servico_nome'] . '<br>' .
                         'Técnico: ' . $agendamento['tecnico_nome'] . '<br>' .
                         'Status: ' . ucfirst($agendamento['status'])
    ];
}

// Retornar JSON
header('Content-Type: application/json');
echo json_encode($events);
