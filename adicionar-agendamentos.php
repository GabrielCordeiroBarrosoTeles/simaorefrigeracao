<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado como administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Conectar ao banco de dados
$db = db_connect();

// Função para criar agendamentos de exemplo
function criar_agendamentos_exemplo($db) {
    // Verificar se já existem agendamentos
    $query = "SELECT COUNT(*) as total FROM agendamentos";
    $stmt = $db->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        return ['success' => false, 'message' => 'Já existem agendamentos no sistema'];
    }
    
    // Obter técnicos
    $query = "SELECT id FROM tecnicos";
    $stmt = $db->query($query);
    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tecnicos)) {
        return ['success' => false, 'message' => 'Nenhum técnico encontrado'];
    }
    
    // Obter clientes ou criar alguns se não existirem
    $query = "SELECT id FROM clientes";
    $stmt = $db->query($query);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        // Criar alguns clientes de exemplo
        $clientes_exemplo = [
            ['nome' => 'João Silva', 'email' => 'joao@exemplo.com', 'telefone' => '(11) 98765-4321', 'endereco' => 'Rua A, 123'],
            ['nome' => 'Maria Oliveira', 'email' => 'maria@exemplo.com', 'telefone' => '(11) 91234-5678', 'endereco' => 'Av. B, 456'],
            ['nome' => 'Pedro Santos', 'email' => 'pedro@exemplo.com', 'telefone' => '(11) 92345-6789', 'endereco' => 'Rua C, 789'],
            ['nome' => 'Ana Costa', 'email' => 'ana@exemplo.com', 'telefone' => '(11) 93456-7890', 'endereco' => 'Av. D, 101'],
            ['nome' => 'Carlos Souza', 'email' => 'carlos@exemplo.com', 'telefone' => '(11) 94567-8901', 'endereco' => 'Rua E, 202']
        ];
        
        foreach ($clientes_exemplo as $cliente) {
            $query = "INSERT INTO clientes (nome, email, telefone, endereco) VALUES (:nome, :email, :telefone, :endereco)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $cliente['nome']);
            $stmt->bindParam(':email', $cliente['email']);
            $stmt->bindParam(':telefone', $cliente['telefone']);
            $stmt->bindParam(':endereco', $cliente['endereco']);
            $stmt->execute();
        }
        
        // Obter os clientes recém-criados
        $query = "SELECT id FROM clientes";
        $stmt = $db->query($query);
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obter serviços ou criar alguns se não existirem
    $query = "SELECT id FROM servicos";
    $stmt = $db->query($query);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($servicos)) {
        // Criar alguns serviços de exemplo
        $servicos_exemplo = [
            ['titulo' => 'Instalação de Ar Condicionado', 'descricao' => 'Instalação completa de ar condicionado split', 'preco' => 350.00],
            ['titulo' => 'Manutenção Preventiva', 'descricao' => 'Limpeza e verificação de funcionamento', 'preco' => 150.00],
            ['titulo' => 'Reparo de Ar Condicionado', 'descricao' => 'Diagnóstico e reparo de problemas', 'preco' => 200.00],
            ['titulo' => 'Troca de Gás', 'descricao' => 'Recarga de gás refrigerante', 'preco' => 180.00],
            ['titulo' => 'Instalação de Ar Condicionado Janela', 'descricao' => 'Instalação de ar condicionado tipo janela', 'preco' => 250.00]
        ];
        
        foreach ($servicos_exemplo as $servico) {
            $query = "INSERT INTO servicos (titulo, descricao, preco) VALUES (:titulo, :descricao, :preco)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':titulo', $servico['titulo']);
            $stmt->bindParam(':descricao', $servico['descricao']);
            $stmt->bindParam(':preco', $servico['preco']);
            $stmt->execute();
        }
        
        // Obter os serviços recém-criados
        $query = "SELECT id FROM servicos";
        $stmt = $db->query($query);
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Criar agendamentos de exemplo
    $agendamentos_criados = 0;
    $status_opcoes = ['pendente', 'concluido', 'cancelado'];
    
    // Data atual
    $data_atual = new DateTime();
    
    // Criar agendamentos para os próximos 30 dias
    for ($i = -15; $i <= 30; $i++) {
        $data = clone $data_atual;
        $data->modify("$i day");
        
        // Pular finais de semana
        $dia_semana = $data->format('N');
        if ($dia_semana >= 6) { // 6 = sábado, 7 = domingo
            continue;
        }
        
        // Criar 2-4 agendamentos por dia
        $num_agendamentos = rand(2, 4);
        
        for ($j = 0; $j < $num_agendamentos; $j++) {
            // Selecionar técnico, cliente e serviço aleatórios
            $tecnico = $tecnicos[array_rand($tecnicos)];
            $cliente = $clientes[array_rand($clientes)];
            $servico = $servicos[array_rand($servicos)];
            
            // Definir horário aleatório entre 8h e 17h
            $hora = rand(8, 17);
            $minuto = rand(0, 3) * 15; // 0, 15, 30, 45
            $hora_inicio = sprintf('%02d:%02d:00', $hora, $minuto);
            
            // Duração aleatória entre 1h e 3h
            $duracao = rand(1, 3);
            $hora_fim = sprintf('%02d:%02d:00', $hora + $duracao, $minuto);
            
            // Status: agendamentos passados podem ser concluídos ou cancelados, futuros são pendentes
            if ($i < 0) {
                $status = $status_opcoes[array_rand([$status_opcoes[1], $status_opcoes[2]])]; // concluído ou cancelado
            } else {
                $status = 'pendente';
            }
            
            // Título do agendamento
            $titulo = "Agendamento #" . ($agendamentos_criados + 1);
            
            // Observações
            $observacoes = "Agendamento de exemplo criado automaticamente.";
            
            // Inserir agendamento
            $query = "INSERT INTO agendamentos (tecnico_id, cliente_id, servico_id, titulo, data_agendamento, hora_inicio, hora_fim, status, observacoes) 
                      VALUES (:tecnico_id, :cliente_id, :servico_id, :titulo, :data_agendamento, :hora_inicio, :hora_fim, :status, :observacoes)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tecnico_id', $tecnico['id']);
            $stmt->bindParam(':cliente_id', $cliente['id']);
            $stmt->bindParam(':servico_id', $servico['id']);
            $stmt->bindParam(':titulo', $titulo);
            $data_str = $data->format('Y-m-d');
            $stmt->bindParam(':data_agendamento', $data_str);
            $stmt->bindParam(':hora_inicio', $hora_inicio);
            $stmt->bindParam(':hora_fim', $hora_fim);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':observacoes', $observacoes);
            $stmt->execute();
            
            $agendamentos_criados++;
        }
    }
    
    return ['success' => true, 'message' => "Criados $agendamentos_criados agendamentos de exemplo"];
}

// Executar a função
try {
    $resultado = criar_agendamentos_exemplo($db);
    header('Content-Type: application/json');
    echo json_encode($resultado);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}