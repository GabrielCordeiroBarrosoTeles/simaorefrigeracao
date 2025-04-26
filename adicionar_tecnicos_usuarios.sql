-- Adicionar novos níveis de usuário
ALTER TABLE usuarios 
MODIFY COLUMN nivel ENUM('admin', 'editor', 'tecnico', 'tecnico_adm') NOT NULL DEFAULT 'editor';

-- Adicionar coluna usuario_id na tabela de técnicos
ALTER TABLE tecnicos 
ADD COLUMN usuario_id INT NULL,
ADD CONSTRAINT fk_tecnico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- Criar índice para melhorar performance de consultas
CREATE INDEX idx_tecnico_usuario ON tecnicos(usuario_id);

-- Criar usuários para técnicos
INSERT INTO usuarios (nome, email, senha, nivel, data_criacao) VALUES
('Carlos Silva', 'carlos@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW()),
('Marcos Oliveira', 'marcos@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW()),
('Pedro Santos', 'pedro@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico_adm', NOW());

-- Associar usuários aos técnicos existentes
UPDATE tecnicos SET usuario_id = (SELECT id FROM usuarios WHERE email = 'carlos@simaorefrigeracao.com.br') WHERE email = 'carlos@simaorefrigeracao.com.br';
UPDATE tecnicos SET usuario_id = (SELECT id FROM usuarios WHERE email = 'marcos@simaorefrigeracao.com.br') WHERE email = 'marcos@simaorefrigeracao.com.br';
UPDATE tecnicos SET usuario_id = (SELECT id FROM usuarios WHERE email = 'pedro@simaorefrigeracao.com.br') WHERE email = 'pedro@simaorefrigeracao.com.br';
