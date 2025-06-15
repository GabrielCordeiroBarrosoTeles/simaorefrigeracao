<?php
// Configuração direta para o banco de dados
$host = 'db';
$dbname = 'simaorefrigeracao';
$user = 'simao';
$password = 'root';

try {
    echo "Conectando ao banco de dados...\n";
    
    // Conectar ao banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexão estabelecida com sucesso!\n";
    
    // Limpar tabelas existentes
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE usuarios");
    $pdo->exec("TRUNCATE TABLE tecnicos");
    $pdo->exec("TRUNCATE TABLE clientes");
    $pdo->exec("TRUNCATE TABLE servicos");
    $pdo->exec("TRUNCATE TABLE agendamentos");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Tabelas limpas com sucesso!\n";
    
    // Inserir usuários
    $pdo->exec("
        INSERT INTO usuarios (id, nome, email, senha, nivel, ultimo_login, data_criacao) VALUES
        (1, 'Administrador', 'admin@simaorefrigeracao.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW()),
        (2, 'Técnico', 'tecnico@simaorefrigeracao.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW(), NOW())
    ");
    echo "Usuários inseridos com sucesso!\n";
    
    // Inserir técnicos
    $pdo->exec("
        INSERT INTO tecnicos (id, nome, email, telefone, especialidade, cor, status, data_criacao, usuario_id) VALUES
        (1, 'Carlos Silva', 'carlos@simaorefrigeracao.com.br', '(85) 98765-4321', 'Instalação', '#3b82f6', 'ativo', NOW(), NULL),
        (2, 'Marcos Oliveira', 'marcos@simaorefrigeracao.com.br', '(85) 98765-4322', 'Manutenção', '#10b981', 'ativo', NOW(), NULL),
        (3, 'Pedro Santos', 'tecnico@simaorefrigeracao.com', '(85) 98765-4323', 'Câmaras Frigoríficas', '#f59e0b', 'ativo', NOW(), 2)
    ");
    echo "Técnicos inseridos com sucesso!\n";
    
    // Inserir clientes
    $pdo->exec("
        INSERT INTO clientes (id, nome, email, telefone, endereco, cidade, estado, cep, tipo, observacoes, data_criacao) VALUES
        (1, 'Supermercado Bom Preço', 'contato@bompreco.com.br', '(85) 3333-4444', 'Av. Comercial, 1000', 'Fortaleza', 'CE', '60000-000', 'comercial', 'Cliente VIP', NOW()),
        (2, 'Restaurante Sabor & Arte', 'contato@saborarte.com.br', '(85) 3333-5555', 'Rua dos Restaurantes, 500', 'Fortaleza', 'CE', '60000-000', 'comercial', NULL, NOW()),
        (3, 'João Pereira', 'joao@email.com', '(85) 99999-8888', 'Rua Residencial, 100, Apto 50', 'Fortaleza', 'CE', '60000-000', 'residencial', NULL, NOW()),
        (4, 'Indústria Alimentos ABC', 'contato@alimentosabc.com.br', '(85) 3333-6666', 'Rodovia Industrial, Km 10', 'Caucaia', 'CE', '60000-000', 'industrial', NULL, NOW())
    ");
    echo "Clientes inseridos com sucesso!\n";
    
    // Inserir serviços
    $pdo->exec("
        INSERT INTO servicos (id, titulo, icone, descricao, itens, data_criacao, garantia_meses) VALUES
        (1, 'Instalação de Ar Condicionado', 'fan', 'Instalação profissional de equipamentos residenciais e comerciais.', '[\"Instalação de splits e multi-splits\", \"Instalação de ar condicionado central\", \"Instalação de VRF/VRV\"]', NOW(), 3),
        (2, 'Manutenção Preventiva', 'thermometer', 'Serviços regulares para garantir o funcionamento ideal do seu equipamento.', '[\"Limpeza de filtros e componentes\", \"Verificação de gás refrigerante\", \"Inspeção de componentes elétricos\"]', NOW(), 3),
        (3, 'Manutenção Corretiva', 'tools', 'Reparo rápido e eficiente para resolver problemas no seu equipamento.', '[\"Diagnóstico preciso de falhas\", \"Reparo de vazamentos\", \"Substituição de componentes\"]', NOW(), 3),
        (4, 'Visita Técnica', 'phone', 'Avaliação profissional para identificar problemas e propor soluções.', '[\"Diagnóstico de problemas\", \"Orçamento detalhado\", \"Recomendações técnicas\"]', NOW(), 3),
        (5, 'Câmara Frigorífica', 'snowflake', 'Soluções para armazenamento refrigerado comercial e industrial.', '[\"Instalação de câmaras frigoríficas\", \"Manutenção de sistemas de refrigeração\", \"Projetos personalizados\"]', NOW(), 3)
    ");
    echo "Serviços inseridos com sucesso!\n";
    
    // Inserir agendamentos
    $pdo->exec("
        INSERT INTO agendamentos (id, titulo, cliente_id, servico_id, tecnico_id, data_agendamento, hora_inicio, hora_fim, observacoes, status, data_criacao) VALUES
        (1, 'Instalação de Split', 1, 1, 1, CURDATE(), '09:00:00', '11:00:00', 'Instalação de 2 aparelhos', 'pendente', NOW()),
        (2, 'Manutenção Preventiva', 2, 2, 2, CURDATE() + INTERVAL 1 DAY, '14:00:00', '16:00:00', NULL, 'pendente', NOW()),
        (3, 'Instalação de Câmara Fria', 4, 5, 3, CURDATE() + INTERVAL 2 DAY, '08:00:00', '17:00:00', 'Câmara de grande porte', 'pendente', NOW()),
        (4, 'Manutenção Corretiva', 3, 3, 3, CURDATE() + INTERVAL 3 DAY, '10:00:00', '12:00:00', 'Vazamento de gás', 'pendente', NOW())
    ");
    echo "Agendamentos inseridos com sucesso!\n";
    
    echo "Seed concluído com sucesso!\n";
    echo "\n";
    echo "Credenciais de acesso:\n";
    echo "Admin: admin@simaorefrigeracao.com / password\n";
    echo "Técnico: tecnico@simaorefrigeracao.com / password\n";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}