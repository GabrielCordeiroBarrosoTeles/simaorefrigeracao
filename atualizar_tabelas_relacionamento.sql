-- Atualizar a tabela de agendamentos para incluir campos de valor, valor pendente e garantia
ALTER TABLE agendamentos 
ADD COLUMN IF NOT EXISTS valor DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor cobrado pelo serviço',
ADD COLUMN IF NOT EXISTS valor_pendente DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Valor pendente de pagamento',
ADD COLUMN IF NOT EXISTS data_garantia DATE NULL COMMENT 'Data de término da garantia',
ADD COLUMN IF NOT EXISTS observacoes_tecnicas TEXT NULL COMMENT 'Observações técnicas do serviço realizado',
ADD COLUMN IF NOT EXISTS local_servico VARCHAR(255) NULL COMMENT 'Local onde o serviço foi realizado';

-- Atualizar a tabela de serviços para incluir campo de garantia em meses
ALTER TABLE servicos 
ADD COLUMN IF NOT EXISTS garantia_meses INT DEFAULT 3 COMMENT 'Duração da garantia em meses';

-- Atualizar a tabela de clientes para incluir campos adicionais
ALTER TABLE clientes
ADD COLUMN IF NOT EXISTS tipo ENUM('residencial', 'comercial', 'industrial') DEFAULT 'residencial' COMMENT 'Tipo de cliente';

-- Atualizar a tabela de técnicos para incluir campos adicionais
ALTER TABLE tecnicos
ADD COLUMN IF NOT EXISTS cor VARCHAR(20) DEFAULT '#3b82f6' COMMENT 'Cor para identificação no calendário',
ADD COLUMN IF NOT EXISTS especialidade VARCHAR(100) NULL COMMENT 'Especialidade do técnico';

-- Criar tabela de histórico de serviços
CREATE TABLE IF NOT EXISTS historico_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT NOT NULL,
    cliente_id INT NOT NULL,
    tecnico_id INT NOT NULL,
    servico_id INT NOT NULL,
    data_servico DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NULL,
    valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_pendente DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pendente', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente',
    observacoes TEXT NULL,
    observacoes_tecnicas TEXT NULL,
    local_servico VARCHAR(255) NULL,
    data_garantia DATE NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE
);

-- Criar índices para melhorar a performance
CREATE INDEX IF NOT EXISTS idx_agendamentos_cliente ON agendamentos(cliente_id);
CREATE INDEX IF NOT EXISTS idx_agendamentos_tecnico ON agendamentos(tecnico_id);
CREATE INDEX IF NOT EXISTS idx_agendamentos_servico ON agendamentos(servico_id);
CREATE INDEX IF NOT EXISTS idx_agendamentos_data ON agendamentos(data_agendamento);
CREATE INDEX IF NOT EXISTS idx_agendamentos_status ON agendamentos(status);
