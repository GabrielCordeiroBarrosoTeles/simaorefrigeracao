<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar se o usuário é um técnico
if ($_SESSION['user_nivel'] !== 'tecnico' && $_SESSION['user_nivel'] !== 'tecnico_adm' && $_SESSION['user_nivel'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Conectar ao banco de dados
$db = db_connect();

// Processar solicitações
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'get_events':
        getEvents($db);
        break;
    case 'update_status':
        updateStatus($db);
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
        break;
}

// Função para obter eventos do calendário
function getEvents($db) {
    $tecnico_id = isset($_GET['tecnico_id']) ? (int)$_GET['tecnico_id'] : null;
    $start = isset($_GET['start']) ? $_GET['start'] : null;
    $end = isset($_GET['end']) ? $_GET['end'] : null;
    
    try {
        // Construir a consulta base
        $query = "SELECT a.*, 
                 c.nome as cliente_nome, 
                 s.nome as servico_nome 
                 FROM agendamentos a 
                 LEFT JOIN clientes c ON a.cliente_id = c.id 
                 LEFT JOIN servicos s ON a.servico_id = s.id 
                 WHERE 1=1";
        
        $params = [];
        
        // Adicionar filtro por técnico se fornecido
        if ($tecnico_id) {
            $query .= " AND a.tecnico_id = :tecnico_id";
            $params[':tecnico_id'] = $tecnico_id;
        }
        
        // Adicionar filtro por data se fornecido
        if ($start) {
            $query .= " AND a.data_agendamento >= :start";
            $params[':start'] = date('Y-m-d', strtotime($start));
        }
        
        if ($end) {
            $query .= " AND a.data_agendamento <= :end";
            $params[':end'] = date('Y-m-d', strtotime($end));
        }
        
        // Ordenar por data
        $query .= " ORDER BY a.data_agendamento ASC";
        
        $stmt = $db->prepare($query);
        
        // Vincular parâmetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Processar eventos para o formato do calendário
        $formattedEvents = [];
        foreach ($events as $event) {
            // Mapear status para valores legíveis
            $status = 'pendente';
            switch ($event['status']) {
                case 'p':
                    $status = 'pendente';
                    break;
                case 'a':
                    $status = 'em_andamento';
                    break;
                case 'c':
                    $status = 'concluido';
                    break;
                case 'x':
                    $status = 'cancelado';
                    break;
            }
            
            // Criar título do evento
            $titulo = $event['cliente_nome'] . ' - ' . $event['servico_nome'];
            
            // Adicionar ao array de eventos formatados
            $formattedEvents[] = [
                'id' => $event['id'],
                'titulo' => $titulo,
                'data_agendamento' => date('Y-m-d', strtotime($event['data_agendamento'])),
                'hora_inicio' => date('H:i:s', strtotime($event['data_agendamento'])),
                'hora_fim' => isset($event['data_fim']) ? date('H:i:s', strtotime($event['data_fim'])) : null,
                'cliente_nome' => $event['cliente_nome'],
                'servico_nome' => $event['servico_nome'],
                'status' => $status,
                'observacoes' => $event['observacoes'] ?? ''
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $formattedEvents]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar eventos: ' . $e->getMessage()]);
    }
}

// Função para atualizar o status de um agendamento
function updateStatus($db) {
    $agendamento_id = isset($_POST['agendamento_id']) ? (int)$_POST['agendamento_id'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    
    if (!$agendamento_id || !$status) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
        return;
    }
    
    try {
        // Converter status para formato de caractere único
        $status_char = 'p'; // padrão: pendente
        
        switch($status) {
            case 'pendente': 
                $status_char = 'p'; 
                break;
            case 'em_andamento': 
                $status_char = 'a'; 
                break;
            case 'concluido': 
                $status_char = 'c'; 
                break;
            case 'cancelado': 
                $status_char = 'x'; 
                break;
        }
        
        $query = "UPDATE agendamentos SET status = :status, data_atualizacao = NOW() WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status_char);
        $stmt->bindParam(':id', $agendamento_id);
        $stmt->execute();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
    }
}