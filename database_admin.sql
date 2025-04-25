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

-- Inserir técnicos iniciais
INSERT INTO tecnicos (nome, email, telefone, especialidade, cor, status, data_criacao) VALUES
('Carlos Silva', 'carlos@friocerto.com.br', '(11) 98765-4321', 'Instalação', '#3b82f6', 'ativo', NOW()),
('Marcos Oliveira', 'marcos@friocerto.com.br', '(11) 98765-4322', 'Manutenção', '#10b981', 'ativo', NOW()),
('Pedro Santos', 'pedro@friocerto.com.br', '(11) 98765-4323', 'Câmaras Frigoríficas', '#f59e0b', 'ativo', NOW());

-- Inserir clientes iniciais
INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, tipo, data_criacao) VALUES
('Supermercado Bom Preço', 'contato@bompreco.com.br', '(11) 3333-4444', 'Av. Comercial, 1000', 'São Paulo', 'SP', 'comercial', NOW()),
('Restaurante Sabor & Arte', 'contato@saborarte.com.br', '(11) 3333-5555', 'Rua dos Restaurantes, 500', 'São Paulo', 'SP', 'comercial', NOW()),
('João Pereira', 'joao@email.com', '(11) 99999-8888', 'Rua Residencial, 100, Apto 50', 'São Paulo', 'SP', 'residencial', NOW()),
('Indústria Alimentos ABC', 'contato@alimentosabc.com.br', '(11) 3333-6666', 'Rodovia Industrial, Km 10', 'Guarulhos', 'SP', 'industrial', NOW());

-- Inserir agendamentos iniciais
INSERT INTO agendamentos (titulo, cliente_id, servico_id, tecnico_id, data_agendamento, hora_inicio, hora_fim, status, data_criacao) VALUES
('Instalação de Ar Split', 3, 1, 1, CURDATE(), '09:00', '11:00', 'pendente', NOW()),
('Manutenção Preventiva', 1, 2, 2, CURDATE(), '14:00', '16:00', 'pendente', NOW()),
('Instalação de Câmara Fria', 4, 5, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00', '17:00', 'pendente', NOW()),
('Manutenção Corretiva', 2, 3, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00', '12:00', 'pendente', NOW());
