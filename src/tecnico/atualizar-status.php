<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php?tecnico=1');
    exit;
}

// Verificar se o usuário é um técnico
if ($_SESSION['user_nivel'] !== 'tecnico' && $_SESSION['user_nivel'] !== 'tecnico_adm') {
    header('Location: admin-dashboard.php');
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tecnico-agendamentos.php');
    exit;
}

// Obter dados do formulário
$agendamento_id = isset($_POST['agendamento_id']) ? intval($_POST['agendamento_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : '';

// Validar status
if (!in_array($status, ['pendente', 'concluido', 'cancelado'])) {
    set_flash_message('danger', 'Status inválido.');
    header('Location: tecnico-agendamentos.php');
    exit;
}

// Conectar ao banco de dados
$db = db_connect();

try {
    // Verificar se o agendamento existe e pertence ao técnico
    $query = "SELECT a.* FROM agendamentos a 
             INNER JOIN tecnicos t ON a.tecnico_id = t.id 
             WHERE a.id = :agendamento_id AND t.usuario_id = :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':agendamento_id', $agendamento_id);
    $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt->execute();
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        set_flash_message('danger', 'Agendamento não encontrado ou não pertence a este técnico.');
        header('Location: tecnico-agendamentos.php');
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
    
    // Redirecionar com mensagem de sucesso
    set_flash_message('success', 'Status do agendamento atualizado com sucesso.');
    header('Location: tecnico-agendamentos.php');
    exit;
} catch (Exception $e) {
    set_flash_message('danger', 'Erro ao atualizar status: ' . $e->getMessage());
    header('Location: tecnico-agendamentos.php');
    exit;
}