-- Criar tabela de usuários se não existir
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'tecnico', 'tecnico_adm') NOT NULL DEFAULT 'admin',
    ultimo_login DATETIME,
    remember_token VARCHAR(100),
    token_expiry BIGINT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criar tabela de clientes se não existir
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(100),
    estado CHAR(2),
    cep VARCHAR(10),
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criar tabela de técnicos se não existir
CREATE TABLE IF NOT EXISTS tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    especialidade VARCHAR(100),
    usuario_id INT,
    disponivel BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Criar tabela de serviços se não existir
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2),
    duracao INT, -- em minutos
    ativo BOOLEAN DEFAULT TRUE,
    destaque BOOLEAN DEFAULT FALSE,
    imagem VARCHAR(255),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criar tabela de agendamentos se não existir
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    tecnico_id INT,
    servico_id INT,
    data_agendamento DATETIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL
);

-- Criar tabela de contatos se não existir
CREATE TABLE IF NOT EXISTS contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    assunto VARCHAR(100),
    mensagem TEXT NOT NULL,
    status ENUM('novo', 'lido', 'respondido', 'arquivado') DEFAULT 'novo',
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuários padrão se não existirem
INSERT INTO usuarios (nome, email, senha, nivel, ultimo_login)
SELECT 'Administrador', 'admin@friocerto.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@friocerto.com.br');

INSERT INTO usuarios (nome, email, senha, nivel, ultimo_login)
SELECT 'Administrador 2', 'simaorefrigeracao2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'simaorefrigeracao2@gmail.com');

INSERT INTO usuarios (nome, email, senha, nivel, ultimo_login)
SELECT 'Carlos', 'carlos@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW()
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'carlos@simaorefrigeracao.com.br');

-- Inserir alguns dados de exemplo para testes
-- Clientes de exemplo
INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, cep)
VALUES 
('João Silva', 'joao@example.com', '(11) 98765-4321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567'),
('Maria Oliveira', 'maria@example.com', '(11) 91234-5678', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100'),
('Pedro Santos', 'pedro@example.com', '(11) 99876-5432', 'Rua Augusta, 500', 'São Paulo', 'SP', '01305-000');

-- Serviços de exemplo
INSERT INTO servicos (nome, descricao, preco, duracao, ativo, destaque)
VALUES 
('Instalação de Ar Condicionado', 'Instalação completa de aparelhos de ar condicionado', 350.00, 120, TRUE, TRUE),
('Manutenção Preventiva', 'Limpeza e verificação do funcionamento do aparelho', 150.00, 60, TRUE, TRUE),
('Reparo de Ar Condicionado', 'Conserto de aparelhos com defeito', 200.00, 90, TRUE, FALSE);

-- Técnicos de exemplo
INSERT INTO tecnicos (nome, email, telefone, especialidade, usuario_id, disponivel)
VALUES 
('Carlos Técnico', 'carlos@simaorefrigeracao.com.br', '(11) 97777-8888', 'Instalação e Manutenção', 
 (SELECT id FROM usuarios WHERE email = 'carlos@simaorefrigeracao.com.br'), TRUE);
