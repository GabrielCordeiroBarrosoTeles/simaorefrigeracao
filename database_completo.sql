-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS friocerto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE friocerto;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    ultimo_login DATETIME,
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME
);

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    icone VARCHAR(50),
    descricao TEXT NOT NULL,
    itens JSON NOT NULL,
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME
);

-- Tabela de depoimentos
CREATE TABLE IF NOT EXISTS depoimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    texto TEXT NOT NULL,
    foto VARCHAR(255),
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME
);

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    tipo ENUM('residencial', 'comercial', 'industrial') NOT NULL DEFAULT 'residencial',
    observacoes TEXT,
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME
);

-- Tabela de técnicos
CREATE TABLE IF NOT EXISTS tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    especialidade VARCHAR(100),
    cor VARCHAR(7) DEFAULT '#3b82f6',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME
);

-- Tabela de contatos
CREATE TABLE IF NOT EXISTS contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    servico_id INT,
    mensagem TEXT NOT NULL,
    status ENUM('novo', 'em_andamento', 'respondido', 'finalizado') NOT NULL DEFAULT 'novo',
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL
);

-- Tabela de estatísticas
CREATE TABLE IF NOT EXISTS estatisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    valor VARCHAR(20) NOT NULL,
    descricao VARCHAR(100) NOT NULL,
    ordem INT NOT NULL DEFAULT 0
);

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT PRIMARY KEY DEFAULT 1,
    nome_empresa VARCHAR(100) NOT NULL,
    descricao_empresa TEXT,
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco TEXT,
    titulo_hero VARCHAR(100),
    subtitulo_hero TEXT,
    imagem_hero VARCHAR(255),
    imagem_sobre VARCHAR(255),
    link_sobre VARCHAR(255),
    facebook VARCHAR(255),
    instagram VARCHAR(255),
    linkedin VARCHAR(255),
    whatsapp VARCHAR(255),
    data_atualizacao DATETIME
);

-- Tabela de agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    cliente_id INT NOT NULL,
    servico_id INT NOT NULL,
    tecnico_id INT NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME,
    observacoes TEXT,
    status ENUM('pendente', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE
);

-- Inserir usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, nivel, data_criacao)
VALUES ('Administrador', 'simaorefrigeracao2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW());

-- Inserir serviços iniciais
INSERT INTO servicos (titulo, icone, descricao, itens, data_criacao) VALUES
('Instalação de Ar Condicionado', 'fan', 'Instalação profissional de equipamentos residenciais e comerciais.', '["Instalação de splits e multi-splits", "Instalação de ar condicionado central", "Instalação de VRF/VRV"]', NOW()),
('Manutenção Preventiva', 'thermometer', 'Serviços regulares para garantir o funcionamento ideal do seu equipamento.', '["Limpeza de filtros e componentes", "Verificação de gás refrigerante", "Inspeção de componentes elétricos"]', NOW()),
('Manutenção Corretiva', 'tools', 'Reparo rápido e eficiente para resolver problemas no seu equipamento.', '["Diagnóstico preciso de falhas", "Reparo de vazamentos", "Substituição de componentes"]', NOW()),
('Visita Técnica', 'phone', 'Avaliação profissional para identificar problemas e propor soluções.', '["Diagnóstico de problemas", "Orçamento detalhado", "Recomendações técnicas"]', NOW()),
('Câmara Frigorífica', 'snowflake', 'Soluções para armazenamento refrigerado comercial e industrial.', '["Instalação de câmaras frigoríficas", "Manutenção de sistemas de refrigeração", "Projetos personalizados"]', NOW()),
('Projetos', 'file-text', 'Desenvolvimento de projetos de climatização para diversos ambientes.', '["Projetos para residências", "Projetos para comércios", "Projetos para indústrias"]', NOW());

-- Inserir depoimentos iniciais
INSERT INTO depoimentos (nome, tipo, texto, ativo, data_criacao) VALUES
('João Carlos', 'Residencial', 'Excelente serviço! A equipe foi pontual, profissional e deixou tudo limpo após a instalação. O ar condicionado está funcionando perfeitamente.', 1, NOW()),
('Maria Silva', 'Comercial', 'Contratamos para a manutenção dos equipamentos da nossa loja e o resultado foi excelente. Recomendo para todos que precisam de serviços de qualidade.', 1, NOW()),
('Roberto Lima', 'Industrial', 'Projeto de climatização para nossa fábrica executado com perfeição. Equipe técnica altamente qualificada e comprometida com prazos.', 1, NOW());

-- Inserir estatísticas iniciais
INSERT INTO estatisticas (valor, descricao, ordem) VALUES
('10+', 'Anos de experiência', 1),
('500+', 'Clientes satisfeitos', 2),
('1000+', 'Projetos realizados', 3),
('24h', 'Atendimento', 4);

-- Inserir configurações iniciais com dados da Simão Refrigeração
INSERT INTO configuracoes (id, nome_empresa, descricao_empresa, telefone, email, endereco, titulo_hero, subtitulo_hero, data_atualizacao)
VALUES (1, 'Simão Refrigeração', 'Somos uma empresa especializada em soluções de climatização, com anos de experiência no mercado. Nossa equipe é formada por profissionais qualificados e comprometidos com a excelência.', '(85) 98810-6463', 'simaorefrigeracao2@gmail.com', 'Av. Sabino Monte, 3878 - São João do Tauape, Fortaleza - CE', 'Soluções completas em ar condicionado', 'Oferecemos serviços de instalação, manutenção e projetos para garantir o conforto térmico ideal para sua casa ou empresa.', NOW());

-- Inserir técnicos iniciais
INSERT INTO tecnicos (nome, email, telefone, especialidade, cor, status, data_criacao) VALUES
('Carlos Silva', 'carlos@simaorefrigeracao.com.br', '(85) 98765-4321', 'Instalação', '#3b82f6', 'ativo', NOW()),
('Marcos Oliveira', 'marcos@simaorefrigeracao.com.br', '(85) 98765-4322', 'Manutenção', '#10b981', 'ativo', NOW()),
('Pedro Santos', 'pedro@simaorefrigeracao.com.br', '(85) 98765-4323', 'Câmaras Frigoríficas', '#f59e0b', 'ativo', NOW());

-- Inserir clientes iniciais
INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, tipo, data_criacao) VALUES
('Supermercado Bom Preço', 'contato@bompreco.com.br', '(85) 3333-4444', 'Av. Comercial, 1000', 'Fortaleza', 'CE', 'comercial', NOW()),
('Restaurante Sabor & Arte', 'contato@saborarte.com.br', '(85) 3333-5555', 'Rua dos Restaurantes, 500', 'Fortaleza', 'CE', 'comercial', NOW()),
('João Pereira', 'joao@email.com', '(85) 99999-8888', 'Rua Residencial, 100, Apto 50', 'Fortaleza', 'CE', 'residencial', NOW()),
('Indústria Alimentos ABC', 'contato@alimentosabc.com.br', '(85) 3333-6666', 'Rodovia Industrial, Km 10', 'Caucaia', 'CE', 'industrial', NOW());

-- Inserir agendamentos iniciais
INSERT INTO agendamentos (titulo, cliente_id, servico_id, tecnico_id, data_agendamento, hora_inicio, hora_fim, status, data_criacao) VALUES
('Instalação de Ar Split', 3, 1, 1, CURDATE(), '09:00', '11:00', 'pendente', NOW()),
('Manutenção Preventiva', 1, 2, 2, CURDATE(), '14:00', '16:00', 'pendente', NOW()),
('Instalação de Câmara Fria', 4, 5, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00', '17:00', 'pendente', NOW()),
('Manutenção Corretiva', 2, 3, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00', '12:00', 'pendente', NOW());
