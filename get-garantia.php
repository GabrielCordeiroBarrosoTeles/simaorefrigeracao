<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se foi fornecido um ID de serviço
$servico_id = isset($_GET['servico_id']) ? (int)$_GET['servico_id'] : 0;

if ($servico_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de serviço inválido']);
    exit;
}

// Conexão com o banco de dados
$db = db_connect();

try {
    // Buscar garantia do serviço
    $query = "SELECT garantia_meses FROM servicos WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $servico_id);
    $stmt->execute();
    
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Serviço não encontrado']);
        exit;
    }
    
    // Calcular data de garantia
    $garantia_meses = $servico['garantia_meses'] ?? 3; // Padrão de 3 meses
    $data_atual = new DateTime();
    $data_garantia = clone $data_atual;
    $data_garantia->modify("+{$garantia_meses} months");
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'garantia_meses' => $garantia_meses,
        'data_garantia' => $data_garantia->format('Y-m-d')
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar garantia: ' . $e->getMessage()]);
}
