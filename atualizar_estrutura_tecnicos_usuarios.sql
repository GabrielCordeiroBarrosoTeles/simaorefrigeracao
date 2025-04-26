-- Modificar a tabela de usuários para incluir o novo nível tecnico_adm
ALTER TABLE usuarios 
MODIFY COLUMN nivel ENUM('admin', 'editor', 'tecnico', 'tecnico_adm') NOT NULL DEFAULT 'editor';

-- Adicionar coluna usuario_id na tabela de técnicos para relacionar com a tabela de usuários
ALTER TABLE tecnicos 
ADD COLUMN usuario_id INT NULL,
ADD CONSTRAINT fk_tecnico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- Criar índice para melhorar performance de consultas
CREATE INDEX idx_tecnico_usuario ON tecnicos(usuario_id);
