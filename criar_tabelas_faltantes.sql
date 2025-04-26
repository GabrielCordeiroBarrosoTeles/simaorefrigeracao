-- Criar tabela clientes se não existir
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

-- Criar tabela tecnicos se não existir
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

-- Criar tabela agendamentos se não existir
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

-- Inserir dados de exemplo
-- Cliente de exemplo
INSERT INTO clientes (nome, email, telefone, endereco, cidade, estado, cep, tipo, observacoes)
VALUES ('João Silva', 'joao.silva@email.com', '(11) 98765-4321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 'residencial', 'Cliente preferencial');

-- Técnico de exemplo
INSERT INTO tecnicos (nome, email, telefone, especialidade, cor, status)
VALUES ('Carlos Oliveira', 'carlos.oliveira@email.com', '(11) 91234-5678', 'Instalação e Manutenção', '#3b82f6', 'ativo');

-- Agendamento de exemplo
INSERT INTO agendamentos (titulo, cliente_id, servico_id, tecnico_id, data_agendamento, hora_inicio, hora_fim, observacoes, status)
VALUES ('Instalação de Ar Condicionado', 1, 1, 1, CURDATE(), '10:00:00', '12:00:00', 'Cliente solicitou instalação com urgência', 'pendente');
