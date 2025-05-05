<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin/login');
}

// Verificar se foi fornecido um ID de agendamento
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash_message('danger', 'ID de agendamento inválido.');
    redirect('/admin-table.php?table=agendamentos');
}

// Conexão com o banco de dados
$db = db_connect();

try {
    // Buscar dados do agendamento
    $query = "SELECT a.*, 
                     c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone, c.endereco as cliente_endereco,
                     s.nome as servico_nome, s.descricao as servico_descricao,
                     t.nome as tecnico_nome, t.email as tecnico_email, t.telefone as tecnico_telefone, t.especialidade as tecnico_especialidade
              FROM agendamentos a
              LEFT JOIN clientes c ON a.cliente_id = c.id
              LEFT JOIN servicos s ON a.servico_id = s.id
              LEFT JOIN tecnicos t ON a.tecnico_id = t.id
              WHERE a.id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        set_flash_message('danger', 'Agendamento não encontrado.');
        redirect('/admin-table.php?table=agendamentos');
    }
    
    // Criar XML
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><servico></servico>');
    
    // Adicionar informações do agendamento
    $xml->addChild('id', $agendamento['id']);
    $xml->addChild('data', $agendamento['data_agendamento']);
    $xml->addChild('hora_inicio', $agendamento['hora_inicio']);
    $xml->addChild('hora_fim', $agendamento['hora_fim'] ?? '');
    $xml->addChild('status', $agendamento['status']);
    $xml->addChild('valor', $agendamento['valor'] ?? '0.00');
    $xml->addChild('valor_pendente', $agendamento['valor_pendente'] ?? '0.00');
    $xml->addChild('local', $agendamento['local_servico'] ?? '');
    $xml->addChild('observacoes', $agendamento['observacoes'] ?? '');
    $xml->addChild('observacoes_tecnicas', $agendamento['observacoes_tecnicas'] ?? '');
    
    // Adicionar data de garantia
    $data_garantia = $agendamento['data_garantia'] ?? '';
    $xml->addChild('data_garantia', $data_garantia);
    
    // Adicionar informações do cliente
    $cliente = $xml->addChild('cliente');
    $cliente->addChild('id', $agendamento['cliente_id']);
    $cliente->addChild('nome', $agendamento['cliente_nome']);
    $cliente->addChild('email', $agendamento['cliente_email']);
    $cliente->addChild('telefone', $agendamento['cliente_telefone']);
    $cliente->addChild('endereco', $agendamento['cliente_endereco'] ?? '');
    
    // Adicionar informações do serviço
    $servico = $xml->addChild('servico');
    $servico->addChild('id', $agendamento['servico_id']);
    $servico->addChild('nome', $agendamento['servico_nome']);
    $servico->addChild('descricao', $agendamento['servico_descricao'] ?? '');
    
    // Adicionar informações do técnico
    $tecnico = $xml->addChild('tecnico');
    $tecnico->addChild('id', $agendamento['tecnico_id']);
    $tecnico->addChild('nome', $agendamento['tecnico_nome']);
    $tecnico->addChild('email', $agendamento['tecnico_email']);
    $tecnico->addChild('telefone', $agendamento['tecnico_telefone']);
    $tecnico->addChild('especialidade', $agendamento['tecnico_especialidade'] ?? '');
    
    // Configurar cabeçalhos para download
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="servico_' . $agendamento['id'] . '.xml"');
    header('Cache-Control: max-age=0');
    
    // Saída do XML
    echo $xml->asXML();
    exit;
    
} catch (PDOException $e) {
    set_flash_message('danger', 'Erro ao gerar XML: ' . $e->getMessage());
    redirect('/admin-table.php?table=agendamentos');
}
