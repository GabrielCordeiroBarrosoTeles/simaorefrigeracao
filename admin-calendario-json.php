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

// Definir cabeçalho JSON
header('Content-Type: application/json');

// Obter ação
$action = isset($_GET['action']) ? $_GET['action'] : 'get_events';

// Processar ação
switch ($action) {
    case 'get_details':
        getAgendamentoDetails();
        break;
    
    case 'get_events':
    default:
        getAgendamentos();
        break;
}

// Função para obter agendamentos para o calendário
function getAgendamentos() {
    global $db;
    
    // Obter parâmetros de filtro
    $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-1 month'));
    $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+1 month'));
    $tecnico_id = isset($_GET['tecnico_id']) ? (int)$_GET['tecnico_id'] : null;
    $servico_id = isset($_GET['servico_id']) ? (int)$_GET['servico_id'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    try {
        // Construir consulta SQL
        $sql = "SELECT a.id, a.titulo, a.data_agendamento, a.hora_inicio, a.hora_fim, 
                a.status, a.observacoes, a.valor_cobrado, a.valor_pago, a.forma_pagamento,
                c.nome as cliente_nome, c.id as cliente_id,
                s.titulo as servico_titulo, s.id as servico_id,
                t.nome as tecnico_nome, t.id as tecnico_id, t.cor as tecnico_cor
                FROM agendamentos a
                LEFT JOIN clientes c ON a.cliente_id = c.id
                LEFT JOIN servicos s ON a.servico_id = s.id
                LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                WHERE 1=1";
        
        $params = [];
        
        // Adicionar filtros
        if ($start) {
            $sql .= " AND a.data_agendamento >= :start";
            $params[':start'] = $start;
        }
        
        if ($end) {
            $sql .= " AND a.data_agendamento <= :end";
            $params[':end'] = $end;
        }
        
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
        
        // Preparar e executar consulta
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        // Obter resultados
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatar para o FullCalendar
        $events = [];
        foreach ($agendamentos as $agendamento) {
            // Definir cor com base no status
            $cor = '#3b82f6'; // Azul padrão
            switch ($agendamento['status']) {
                case 'concluido':
                    $cor = '#10b981'; // Verde
                    break;
                case 'cancelado':
                    $cor = '#ef4444'; // Vermelho
                    break;
                case 'pendente':
                    $cor = '#f59e0b'; // Laranja
                    break;
                case 'confirmado':
                    $cor = '#6366f1'; // Índigo
                    break;
            }
            
            // Usar a cor do técnico se disponível
            if (!empty($agendamento['tecnico_cor'])) {
                $cor = $agendamento['tecnico_cor'];
            }
            
            // Definir horário de início e fim
            $start = $agendamento['data_agendamento'] . 'T' . $agendamento['hora_inicio'];
            $end = $agendamento['data_agendamento'] . 'T' . ($agendamento['hora_fim'] ?? '18:00:00');
            
            // Adicionar evento
            $events[] = [
                'id' => $agendamento['id'],
                'title' => $agendamento['titulo'],
                'start' => $start,
                'end' => $end,
                'color' => $cor,
                'extendedProps' => [
                    'cliente' => $agendamento['cliente_nome'],
                    'cliente_id' => $agendamento['cliente_id'],
                    'servico' => $agendamento['servico_titulo'],
                    'servico_id' => $agendamento['servico_id'],
                    'tecnico' => $agendamento['tecnico_nome'],
                    'tecnico_id' => $agendamento['tecnico_id'],
                    'status' => $agendamento['status'],
                    'observacoes' => $agendamento['observacoes']
                ]
            ];
        }
        
        echo json_encode($events);
    } catch (PDOException $e) {
        echo json_encode([
            'error' => 'Erro ao buscar agendamentos',
            'message' => DEBUG_MODE ? $e->getMessage() : null
        ]);
    }
}

// Função para obter detalhes de um agendamento específico
function getAgendamentoDetails() {
    global $db;
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['error' => 'ID inválido']);
        return;
    }
    
    try {
        // Buscar detalhes do agendamento
        $sql = "SELECT a.*, 
                c.nome as cliente_nome, c.telefone as cliente_telefone, c.email as cliente_email,
                s.titulo as servico_titulo, 
                t.nome as tecnico_nome, t.telefone as tecnico_telefone
                FROM agendamentos a
                LEFT JOIN clientes c ON a.cliente_id = c.id
                LEFT JOIN servicos s ON a.servico_id = s.id
                LEFT JOIN tecnicos t ON a.tecnico_id = t.id
                WHERE a.id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$agendamento) {
            echo json_encode(['error' => 'Agendamento não encontrado']);
            return;
        }
        
        // Buscar técnicos adicionais
        $sql = "SELECT t.id, t.nome 
                FROM agendamento_tecnicos at
                JOIN tecnicos t ON at.tecnico_id = t.id
                WHERE at.agendamento_id = :agendamento_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':agendamento_id', $id);
        $stmt->execute();
        
        $tecnicos_adicionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Adicionar técnicos adicionais ao resultado
        $agendamento['tecnicos_adicionais'] = $tecnicos_adicionais;
        
        echo json_encode($agendamento);
    } catch (PDOException $e) {
        echo json_encode([
            'error' => 'Erro ao buscar detalhes do agendamento',
            'message' => DEBUG_MODE ? $e->getMessage() : null
        ]);
    }
}
