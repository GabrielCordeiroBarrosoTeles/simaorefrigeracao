<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('/admin-login.php');
}

// Verificar se é uma requisição POST
if (!is_post_request()) {
    redirect('/admin-dashboard.php');
}

// Conexão com o banco de dados
$db = db_connect();

// Obter o formulário e ação
$form = isset($_POST['form']) ? $_POST['form'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Processar formulário de depoimento
if ($form === 'depoimento') {
    // Sanitizar dados
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nome = sanitize($_POST['nome'] ?? '');
    $tipo = sanitize($_POST['tipo'] ?? '');
    $texto = sanitize($_POST['texto'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Processar upload de foto
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
        // Criar diretório se não existir
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Gerar nome único para o arquivo
        $foto = uniqid() . '_' . basename($_FILES['foto']['name']);
        $upload_path = $upload_dir . $foto;
        
        // Mover arquivo para o diretório de uploads
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
            set_flash_message('danger', 'Erro ao fazer upload da foto.');
            redirect('/admin-depoimentos.php');
        }
    }
    
    try {
        if ($action === 'create') {
            // Inserir novo depoimento
            $query = "INSERT INTO depoimentos (nome, tipo, texto, foto, ativo, data_criacao) 
                      VALUES (:nome, :tipo, :texto, :foto, :ativo, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':texto', $texto);
            $stmt->bindParam(':foto', $foto);
            $stmt->bindParam(':ativo', $ativo);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Depoimento adicionado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao adicionar depoimento.');
            }
        } elseif ($action === 'update' && $id > 0) {
            // Buscar depoimento atual para verificar foto
            $query = "SELECT foto FROM depoimentos WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $depoimento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Se não foi enviada nova foto, manter a atual
            if ($foto === null && isset($depoimento['foto'])) {
                $foto = $depoimento['foto'];
            }
            
            // Atualizar depoimento
            $query = "UPDATE depoimentos 
                      SET nome = :nome, tipo = :tipo, texto = :texto, 
                      foto = :foto, ativo = :ativo, data_atualizacao = NOW() 
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':texto', $texto);
            $stmt->bindParam(':foto', $foto);
            $stmt->bindParam(':ativo', $ativo);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                set_flash_message('success', 'Depoimento atualizado com sucesso!');
            } else {
                set_flash_message('danger', 'Erro ao atualizar depoimento.');
            }
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Erro ao processar sua solicitação.');
        if (DEBUG_MODE) {
            $_SESSION['error_details'] = $e->getMessage();
        }
    }
    
    redirect('/admin-depoimentos.php');
}

// Processar finalização de agendamento
if ($form === 'finalizar_agendamento') {
    // Sanitizar dados
    $agendamento_id = isset($_POST['agendamento_id']) ? (int)$_POST['agendamento_id'] : 0;
    $valor_cobrado = isset($_POST['valor_cobrado']) ? str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor_cobrado']) : 0;
    $valor_pago = isset($_POST['valor_pago']) ? str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor_pago']) : 0;
    $forma_pagamento = sanitize($_POST['forma_pagamento'] ?? '');
    $garantia_meses = isset($_POST['garantia_meses']) ? (int)$_POST['garantia_meses'] : 3;
    $observacoes = sanitize($_POST['observacoes'] ?? '');
    $tecnicos = isset($_POST['tecnicos']) ? $_POST['tecnicos'] : [];
    
    try {
        // Iniciar transação
        $db->beginTransaction();
        
        // Atualizar agendamento
        $query = "UPDATE agendamentos 
                  SET status = 'concluido', 
                  valor_cobrado = :valor_cobrado, 
                  valor_pago = :valor_pago, 
                  forma_pagamento = :forma_pagamento, 
                  garantia_meses = :garantia_meses, 
                  observacoes = CONCAT(observacoes, '\n\n', :observacoes), 
                  data_conclusao = NOW(), 
                  data_atualizacao = NOW() 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':valor_cobrado', $valor_cobrado);
        $stmt->bindParam(':valor_pago', $valor_pago);
        $stmt->bindParam(':forma_pagamento', $forma_pagamento);
        $stmt->bindParam(':garantia_meses', $garantia_meses);
        $stmt->bindParam(':observacoes', $observacoes);
        $stmt->bindParam(':id', $agendamento_id);
        $stmt->execute();
        
        // Limpar técnicos atuais
        $query = "DELETE FROM agendamento_tecnicos WHERE agendamento_id = :agendamento_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':agendamento_id', $agendamento_id);
        $stmt->execute();
        
        // Adicionar técnicos
        if (!empty($tecnicos)) {
            $query = "INSERT INTO agendamento_tecnicos (agendamento_id, tecnico_id) VALUES (:agendamento_id, :tecnico_id)";
            $stmt = $db->prepare($query);
            
            foreach ($tecnicos as $tecnico_id) {
                $stmt->bindParam(':agendamento_id', $agendamento_id);
                $stmt->bindParam(':tecnico_id', $tecnico_id);
                $stmt->execute();
            }
        }
        
        // Confirmar transação
        $db->commit();
        
        set_flash_message('success', 'Agendamento finalizado com sucesso!');
    } catch (PDOException $e) {
        // Reverter transação em caso de erro
        $db->rollBack();
        
        set_flash_message('danger', 'Erro ao finalizar agendamento.');
        if (DEBUG_MODE) {
            $_SESSION['error_details'] = $e->getMessage();
        }
    }
    
    redirect('/admin/agendamentos');
}

// Redirecionar para o dashboard se nenhum formulário foi processado
redirect('/admin-dashboard.php');
