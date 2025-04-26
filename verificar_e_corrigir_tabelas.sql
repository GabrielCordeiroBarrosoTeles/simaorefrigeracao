-- Verificar e corrigir tabelas

-- Verificar se a tabela clientes existe, se não, criar
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    endereco VARCHAR(255),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    tipo ENUM('residencial', 'comercial', 'industrial') DEFAULT 'residencial',
    observacoes TEXT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- Verificar se a tabela tecnicos existe, se não, criar
CREATE TABLE IF NOT EXISTS tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    especialidade VARCHAR(100),
    cor VARCHAR(20) DEFAULT '#3b82f6',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    usuario_id INT,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Verificar se a tabela servicos existe, se não, criar
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2),
    duracao INT,
    imagem VARCHAR(255),
    destaque BOOLEAN DEFAULT FALSE,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- Verificar se a tabela agendamentos existe, se não, criar
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    cliente_id INT NOT NULL,
    servico_id INT NOT NULL,
    tecnico_id INT,
    data_agendamento DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME,
    observacoes TEXT,
    status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') DEFAULT 'pendente',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL
);

-- Inserir alguns dados de exemplo se as tabelas estiverem vazias

-- Inserir serviços de exemplo se não existirem
INSERT INTO servicos (titulo, descricao, preco, duracao, destaque, status)
SELECT 'Instalação de Ar Condicionado', 'Serviço completo de instalação de ar condicionado split.', 350.00, 120, TRUE, 'ativo'
WHERE NOT EXISTS (SELECT 1 FROM servicos WHERE titulo = 'Instalação de Ar Condicionado');

INSERT INTO servicos (titulo, descricao, preco, duracao, destaque, status)
SELECT 'Manutenção Preventiva', 'Limpeza e verificação do funcionamento do ar condicionado.', 150.00, 60, TRUE, 'ativo'
WHERE NOT EXISTS (SELECT 1 FROM servicos WHERE titulo = 'Manutenção Preventiva');

INSERT INTO servicos (titulo, descricao, preco, duracao, destaque, status)
SELECT 'Reparo de Ar Condicionado', 'Diagnóstico e reparo de problemas no ar condicionado.', 250.00, 90, FALSE, 'ativo'
WHERE NOT EXISTS (SELECT 1 FROM servicos WHERE titulo = 'Reparo de Ar Condicionado');

-- Inserir cliente de exemplo se não existir
INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, cep, tipo)
SELECT 'Cliente Exemplo', 'cliente@exemplo.com', '(11) 98765-4321', 'Rua Exemplo, 123', 'São Paulo', 'SP', '01234-567', 'residencial'
WHERE NOT EXISTS (SELECT 1 FROM clientes LIMIT 1);

-- Inserir técnico de exemplo se não existir
INSERT INTO tecnicos (nome, email, telefone, especialidade, cor, status)
SELECT 'Técnico Exemplo', 'tecnico@exemplo.com', '(11) 91234-5678', 'Instalação e Manutenção', '#3b82f6', 'ativo'
WHERE NOT EXISTS (SELECT 1 FROM tecnicos LIMIT 1);

-- Inserir agendamento de exemplo se não existir
INSERT INTO agendamentos (titulo, cliente_id, servico_id, tecnico_id, data_agendamento, hora_inicio, hora_fim, status)
SELECT 'Instalação de Ar Condicionado', 
       (SELECT id FROM clientes ORDER BY id LIMIT 1), 
       (SELECT id FROM servicos ORDER BY id LIMIT 1), 
       (SELECT id FROM tecnicos ORDER BY id LIMIT 1), 
       CURDATE(), '10:00:00', '12:00:00', 'pendente'
WHERE NOT EXISTS (SELECT 1 FROM agendamentos LIMIT 1);
