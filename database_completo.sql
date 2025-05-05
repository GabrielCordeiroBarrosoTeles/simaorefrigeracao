-- Arquivo SQL consolidado para o sistema Simão Refrigeração
-- Este arquivo contém todas as tabelas e dados necessários para o funcionamento do sistema

-- Criação da base de dados (caso não exista)
CREATE DATABASE IF NOT EXISTS simao_refrigeracao;
USE simao_refrigeracao;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'tecnico', 'cliente') NOT NULL DEFAULT 'cliente',
    status TINYINT(1) NOT NULL DEFAULT 1,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, nivel) 
VALUES ('Administrador', 'admin@simaorefrigeracao.com.br', '$2y$10$8tGIx5XCk5a/LBB9LJqYxeC.Ql2CfVbMJKpVyBJ2hVnwKJaHZZ3Hy', 'admin')
ON DUPLICATE KEY UPDATE nome = 'Administrador', senha = '$2y$10$8tGIx5XCk5a/LBB9LJqYxeC.Ql2CfVbMJKpVyBJ2hVnwKJaHZZ3Hy';

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    endereco VARCHAR(255),
    cidade VARCHAR(100),
    estado CHAR(2),
    cep VARCHAR(10),
    usuario_id INT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de técnicos
CREATE TABLE IF NOT EXISTS tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    especialidade VARCHAR(100),
    disponivel TINYINT(1) NOT NULL DEFAULT 1,
    usuario_id INT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    duracao INT NOT NULL COMMENT 'Duração em minutos',
    imagem VARCHAR(255),
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    servico_id INT NOT NULL,
    tecnico_id INT,
    data_agendamento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    observacoes TEXT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL
);

-- Tabela de contatos
CREATE TABLE IF NOT EXISTS contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    assunto VARCHAR(100),
    mensagem TEXT NOT NULL,
    status ENUM('novo', 'lido', 'respondido') DEFAULT 'novo',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_resposta DATETIME,
    resposta TEXT
);

-- Tabela de depoimentos
CREATE TABLE IF NOT EXISTS depoimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cargo VARCHAR(100),
    empresa VARCHAR(100),
    texto TEXT NOT NULL,
    aprovado TINYINT(1) DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT,
    descricao VARCHAR(255),
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir configurações padrão
INSERT INTO configuracoes (chave, valor, descricao) VALUES
('site_titulo', 'Simão Refrigeração', 'Título do site'),
('site_descricao', 'Serviços de ar condicionado e refrigeração', 'Descrição do site'),
('site_email', 'contato@simaorefrigeracao.com.br', 'E-mail principal do site'),
('site_telefone', '(11) 99999-9999', 'Telefone principal do site'),
('site_endereco', 'Rua Exemplo, 123 - São Paulo/SP', 'Endereço da empresa'),
('horario_funcionamento', 'Segunda a Sexta: 8h às 18h | Sábado: 8h às 12h', 'Horário de funcionamento'),
('facebook_url', 'https://facebook.com/simaorefrigeracao', 'URL do Facebook'),
('instagram_url', 'https://instagram.com/simaorefrigeracao', 'URL do Instagram'),
('whatsapp_numero', '5511999999999', 'Número do WhatsApp com código do país')
ON DUPLICATE KEY UPDATE descricao = VALUES(descricao);

-- Inserir alguns serviços de exemplo
INSERT INTO servicos (nome, descricao, preco, duracao, destaque, ativo) VALUES
('Instalação de Ar Condicionado Split', 'Instalação completa de ar condicionado split com até 3 metros de tubulação.', 350.00, 120, 1, 1),
('Manutenção Preventiva', 'Limpeza completa, verificação de gás e testes de funcionamento.', 180.00, 60, 1, 1),
('Reparo de Ar Condicionado', 'Diagnóstico e reparo de problemas em aparelhos de ar condicionado.', 250.00, 90, 1, 1),
('Recarga de Gás', 'Recarga completa de gás para ar condicionado.', 200.00, 60, 0, 1)
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- Inserir alguns depoimentos de exemplo
INSERT INTO depoimentos (nome, cargo, empresa, texto, aprovado) VALUES
('João Silva', 'Gerente', 'Empresa ABC', 'Excelente serviço! Rápido, eficiente e com ótimo preço. Recomendo a todos.', 1),
('Maria Oliveira', 'Diretora', 'Clínica Saúde', 'Profissionais muito qualificados. Resolveram o problema do ar condicionado da nossa clínica em tempo recorde.', 1),
('Pedro Santos', 'Proprietário', 'Restaurante Sabor', 'Contratamos para fazer a manutenção de todos os aparelhos do restaurante. Serviço de qualidade e atendimento nota 10!', 1)
ON DUPLICATE KEY UPDATE nome = VALUES(nome);
