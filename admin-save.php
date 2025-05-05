<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin/login');
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin-dashboard.php');
}

// Obter dados do formulário
$form = $_POST['form'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Verificar se o formulário é válido
if (!in_array($form, ['cliente', 'tecnico', 'servico', 'agendamento'])) {
    set_flash_message('danger', 'Formulário inválido.');
    redirect('/admin-dashboard.php');
}

// Conexão com o banco de dados
$db = db_connect();

// Processar formulário de cliente
if ($form === 'cliente') {
    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    $endereco = sanitize($_POST['endereco'] ?? '');
    $cidade = sanitize($_POST['cidade'] ?? '');
    $estado = sanitize($_POST['estado'] ?? '');
    $cep = sanitize($_POST['cep'] ?? '');
    $tipo = sanitize($_POST['tipo'] ?? 'residencial');
    $observacoes = sanitize($_POST['observacoes'] ?? '');
    
    // Validação básica
    if (empty($nome) || empty($email) || empty($telefone)) {
        set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
        redirect('/admin-form.php?form=cliente' . ($id > 0 ? "&id={$id}" : ''));
    }
    
    try {
        if ($id > 0) {
            // Atualizar cliente existente
            $query = "UPDATE clientes SET 
                      nome = :nome, email = :email, telefone = :telefone, 
                      endereco = :endereco, cidade = :cidade, estado = :estado, 
                      cep = :cep, tipo = :tipo, observacoes = :observacoes, 
                      data_atualizacao = NOW() 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            // Adicionar novo cliente
            $query = "INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, cep, tipo, observacoes, data_criacao) 
                      VALUES (:nome, :email, :telefone, :endereco, :cidade, :estado, :cep, :tipo, :observacoes, NOW())";
            $stmt = $db->prepare($query);
        }
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':observacoes', $observacoes);
        
        if ($stmt->execute()) {
            set_flash_message('success', 'Cliente ' . ($id > 0 ? 'atualizado' : 'adicionado') . ' com sucesso!');
            redirect('/admin-table.php?table=clientes');
        } else {
            set_flash_message('danger', 'Erro ao ' . ($id > 0 ? 'atualizar' : 'adicionar') . ' cliente.');
            redirect('/admin-form.php?form=cliente' . ($id > 0 ? "&id={$id}" : ''));
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação: ' . $e->getMessage());
        redirect('/admin-form.php?form=cliente' . ($id > 0 ? "&id={$id}" : ''));
    }
}

// Processar formulário de técnico
if ($form === 'tecnico') {
    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    $especialidade = sanitize($_POST['especialidade'] ?? '');
    $cor = sanitize($_POST['cor'] ?? '#3b82f6');
    $status = sanitize($_POST['status'] ?? 'ativo');
    
    // Validação básica
    if (empty($nome) || empty($email) || empty($telefone)) {
        set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
        redirect('/admin-form.php?form=tecnico' . ($id > 0 ? "&id={$id}" : ''));
    }
    
    try {
        if ($id > 0) {
            // Atualizar técnico existente
            $query = "UPDATE tecnicos SET 
                      nome = :nome, email = :email, telefone = :telefone, 
                      especialidade = :especialidade, cor = :cor, status = :status, 
                      data_atualizacao = NOW() 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            // Adicionar novo técnico
            $query = "INSERT INTO tecnicos (nome, email, telefone, especialidade, cor, status, data_criacao) 
                      VALUES (:nome, :email, :telefone, :especialidade, :cor, :status, NOW())";
            $stmt = $db->prepare($query);
        }
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':especialidade', $especialidade);
        $stmt->bindParam(':cor', $cor);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            set_flash_message('success', 'Técnico ' . ($id > 0 ? 'atualizado' : 'adicionado') . ' com sucesso!');
            redirect('/admin-table.php?table=tecnicos');
        } else {
            set_flash_message('danger', 'Erro ao ' . ($id > 0 ? 'atualizar' : 'adicionar') . ' técnico.');
            redirect('/admin-form.php?form=tecnico' . ($id > 0 ? "&id={$id}" : ''));
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação: ' . $e->getMessage());
        redirect('/admin-form.php?form=tecnico' . ($id > 0 ? "&id={$id}" : ''));
    }
}

// Processar formulário de serviço
if ($form === 'servico') {
    $nome = sanitize($_POST['nome'] ?? '');
    $descricao = sanitize($_POST['descricao'] ?? '');
    $preco = isset($_POST['preco']) ? (float)$_POST['preco'] : 0;
    $duracao = isset($_POST['duracao']) ? (int)$_POST['duracao'] : 0;
    $garantia_meses = isset($_POST['garantia_meses']) ? (int)$_POST['garantia_meses'] : 3;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Validação básica
    if (empty($nome) || empty($descricao) || $preco <= 0 || $duracao <= 0) {
        set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
        redirect('/admin-form.php?form=servico' . ($id > 0 ? "&id={$id}" : ''));
    }
    
    try {
        if ($id > 0) {
            // Atualizar serviço existente
            $query = "UPDATE servicos SET 
                      nome = :nome, descricao = :descricao, preco = :preco, 
                      duracao = :duracao, garantia_meses = :garantia_meses, 
                      destaque = :destaque, ativo = :ativo, 
                      data_atualizacao = NOW() 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            // Adicionar novo serviço
            $query = "INSERT INTO servicos (nome, descricao, preco, duracao, garantia_meses, destaque, ativo, data_criacao) 
                      VALUES (:nome, :descricao, :preco, :duracao, :garantia_meses, :destaque, :ativo, NOW())";
            $stmt = $db->prepare($query);
        }
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':duracao', $duracao);
        $stmt->bindParam(':garantia_meses', $garantia_meses);
        $stmt->bindParam(':destaque', $destaque);
        $stmt->bindParam(':ativo', $ativo);
        
        if ($stmt->execute()) {
            set_flash_message('success', 'Serviço ' . ($id > 0 ? 'atualizado' : 'adicionado') . ' com sucesso!');
            redirect('/admin-table.php?table=servicos');
        } else {
            set_flash_message('danger', 'Erro ao ' . ($id > 0 ? 'atualizar' : 'adicionar') . ' serviço.');
            redirect('/admin-form.php?form=servico' . ($id > 0 ? "&id={$id}" : ''));
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação: ' . $e->getMessage());
        redirect('/admin-form.php?form=servico' . ($id > 0 ? "&id={$id}" : ''));
    }
}

// Processar formulário de agendamento
if ($form === 'agendamento') {
    $cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
    $servico_id = isset($_POST['servico_id']) ? (int)$_POST['servico_id'] : 0;
    $tecnico_id = isset($_POST['tecnico_id']) ? (int)$_POST['tecnico_id'] : 0;
    $data_agendamento = sanitize($_POST['data_agendamento'] ?? '');
    $hora_inicio = sanitize($_POST['hora_inicio'] ?? '');
    $hora_fim = sanitize($_POST['hora_fim'] ?? '');
    $local_servico = sanitize($_POST['local_servico'] ?? '');
    $valor = isset($_POST['valor']) ? (float)$_POST['valor'] : 0;
    $valor_pendente = isset($_POST['valor_pendente']) ? (float)$_POST['valor_pendente'] : 0;
    $status = sanitize($_POST['status'] ?? 'pendente');
    $observacoes = sanitize($_POST['observacoes'] ?? '');
    $observacoes_tecnicas = sanitize($_POST['observacoes_tecnicas'] ?? '');
    $data_garantia = sanitize($_POST['data_garantia'] ?? '');
    
    // Validação básica
    if (empty($data_agendamento) || empty($hora_inicio) || $cliente_id <= 0 || $servico_id <= 0 || $tecnico_id <= 0) {
        set_flash_message('danger', 'Por favor, preencha todos os campos obrigatórios.');
        redirect('/admin-form.php?form=agendamento' . ($id > 0 ? "&id={$id}" : ''));
    }
    
    // Se o status for concluído e não houver data de garantia, calcular automaticamente
    if ($status === 'concluido' && empty($data_garantia)) {
        try {
            // Buscar garantia do serviço
            $query = "SELECT garantia_meses FROM servicos WHERE id = :id LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $servico_id);
            $stmt->execute();
            
            $servico = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($servico) {
                $garantia_meses = $servico['garantia_meses'] ?? 3; // Padrão de 3 meses
                $data_conclusao = new DateTime($data_agendamento);
                $data_garantia_obj = clone $data_conclusao;
                $data_garantia_obj->modify("+{$garantia_meses} months");
                $data_garantia = $data_garantia_obj->format('Y-m-d');
            }
        } catch (PDOException $e) {
            // Em caso de erro, continuar sem definir a data de garantia
        }
    }
    
    try {
        if ($id > 0) {
            // Atualizar agendamento existente
            $query = "UPDATE agendamentos SET 
                      cliente_id = :cliente_id, servico_id = :servico_id, tecnico_id = :tecnico_id, 
                      data_agendamento  servico_id = :servico_id, tecnico_id = :tecnico_id, 
                      data_agendamento = :data_agendamento, hora_inicio = :hora_inicio, 
                      hora_fim = :hora_fim, local_servico = :local_servico,
                      valor = :valor, valor_pendente = :valor_pendente,
                      status = :status, observacoes = :observacoes, 
                      observacoes_tecnicas = :observacoes_tecnicas,
                      data_garantia = :data_garantia,
                      data_atualizacao = NOW() 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
        } else {
            // Adicionar novo agendamento
            $query = "INSERT INTO agendamentos (cliente_id, servico_id, tecnico_id, data_agendamento, 
                      hora_inicio, hora_fim, local_servico, valor, valor_pendente,
                      status, observacoes, observacoes_tecnicas, data_garantia, data_criacao) 
                      VALUES (:cliente_id, :servico_id, :tecnico_id, :data_agendamento, 
                      :hora_inicio, :hora_fim, :local_servico, :valor, :valor_pendente,
                      :status, :observacoes, :observacoes_tecnicas, :data_garantia, NOW())";
            $stmt = $db->prepare($query);
        }
        
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':servico_id', $servico_id);
        $stmt->bindParam(':tecnico_id', $tecnico_id);
        $stmt->bindParam(':data_agendamento', $data_agendamento);
        $stmt->bindParam(':hora_inicio', $hora_inicio);
        $stmt->bindParam(':hora_fim', $hora_fim);
        $stmt->bindParam(':local_servico', $local_servico);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':valor_pendente', $valor_pendente);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':observacoes', $observacoes);
        $stmt->bindParam(':observacoes_tecnicas', $observacoes_tecnicas);
        $stmt->bindParam(':data_garantia', $data_garantia);
        
        if ($stmt->execute()) {
            // Se for um novo agendamento ou o status mudou para concluído, registrar no histórico
            if (!$id || ($id > 0 && $status === 'concluido')) {
                $agendamento_id = $id > 0 ? $id : $db->lastInsertId();
                
                // Registrar no histórico de serviços
                $query = "INSERT INTO historico_servicos (agendamento_id, cliente_id, tecnico_id, servico_id, 
                          data_servico, hora_inicio, hora_fim, valor, valor_pendente, status, 
                          observacoes, observacoes_tecnicas, local_servico, data_garantia) 
                          VALUES (:agendamento_id, :cliente_id, :tecnico_id, :servico_id, 
                          :data_servico, :hora_inicio, :hora_fim, :valor, :valor_pendente, :status, 
                          :observacoes, :observacoes_tecnicas, :local_servico, :data_garantia)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':agendamento_id', $agendamento_id);
                $stmt->bindParam(':cliente_id', $cliente_id);
                $stmt->bindParam(':tecnico_id', $tecnico_id);
                $stmt->bindParam(':servico_id', $servico_id);
                $stmt->bindParam(':data_servico', $data_agendamento);
                $stmt->bindParam(':hora_inicio', $hora_inicio);
                $stmt->bindParam(':hora_fim', $hora_fim);
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':valor_pendente', $valor_pendente);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':observacoes', $observacoes);
                $stmt->bindParam(':observacoes_tecnicas', $observacoes_tecnicas);
                $stmt->bindParam(':local_servico', $local_servico);
                $stmt->bindParam(':data_garantia', $data_garantia);
                $stmt->execute();
            }
            
            set_flash_message('success', 'Agendamento ' . ($id > 0 ? 'atualizado' : 'adicionado') . ' com sucesso!');
            redirect('/admin-table.php?table=agendamentos');
        } else {
            set_flash_message('danger', 'Erro ao ' . ($id > 0 ? 'atualizar' : 'adicionar') . ' agendamento.');
            redirect('/admin-form.php?form=agendamento' . ($id > 0 ? "&id={$id}" : ''));
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação: ' . $e->getMessage());
        redirect('/admin-form.php?form=agendamento' . ($id > 0 ? "&id={$id}" : ''));
    }
}

// Se chegou até aqui, redirecionar para o dashboard
redirect('/admin-dashboard.php');
