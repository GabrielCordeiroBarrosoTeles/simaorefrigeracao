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
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se o usuário é um técnico
if ($_SESSION['user_nivel'] !== 'tecnico' && $_SESSION['user_nivel'] !== 'tecnico_adm') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Conectar ao banco de dados
$db = db_connect();

// Processar requisições
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'get_events':
        // Obter eventos para o calendário
        $tecnico_id = isset($_GET['tecnico_id']) ? intval($_GET['tecnico_id']) : 0;
        $start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
        $end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+30 days'));
        
        try {
            $query = "SELECT a.*, 
                     IFNULL((SELECT nome FROM clientes WHERE id = a.cliente_id), 'Cliente não encontrado') as cliente_nome,
                     IFNULL((SELECT titulo FROM servicos WHERE id = a.servico_id), 'Serviço não encontrado') as servico_nome
                     FROM agendamentos a 
                     WHERE a.tecnico_id = :tecnico_id 
                     AND a.data_agendamento BETWEEN :start AND :end
                     ORDER BY a.data_agendamento, a.hora_inicio";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tecnico_id', $tecnico_id);
            $stmt->bindParam(':start', $start);
            $stmt->bindParam(':end', $end);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $events]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar eventos: ' . $e->getMessage()]);
        }
        break;
        
    case 'update_status':
        // Atualizar status do agendamento
        $agendamento_id = isset($_POST['agendamento_id']) ? intval($_POST['agendamento_id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : '';
        
        if (!in_array($status, ['pendente', 'concluido', 'cancelado'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            exit;
        }
        
        try {
            // Verificar se o agendamento pertence ao técnico
            $query = "SELECT a.* FROM agendamentos a 
                     INNER JOIN tecnicos t ON a.tecnico_id = t.id 
                     WHERE a.id = :agendamento_id AND t.usuario_id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':agendamento_id', $agendamento_id);
            $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
            $stmt->execute();
            $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$agendamento) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado ou não pertence a este técnico']);
                exit;
            }
            
            // Atualizar status
            $query = "UPDATE agendamentos SET status = :status";
            
            if (!empty($observacoes)) {
                $query .= ", observacoes = CONCAT(IFNULL(observacoes, ''), '\n', :observacoes)";
            }
            
            $query .= " WHERE id = :agendamento_id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':agendamento_id', $agendamento_id);
            
            if (!empty($observacoes)) {
                $observacao_com_data = date('d/m/Y H:i') . ' - ' . $observacoes;
                $stmt->bindParam(':observacoes', $observacao_com_data);
            }
            
            $stmt->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
        break;
}