-- Verificar se a coluna 'assunto' existe na tabela contatos
SET @exists = 0;
SELECT COUNT(*) INTO @exists FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'contatos' AND column_name = 'assunto';

-- Se a coluna não existir, adicioná-la
SET @query = IF(@exists = 0, 
    'ALTER TABLE contatos ADD COLUMN assunto VARCHAR(100) AFTER telefone',
    'SELECT "Coluna assunto já existe"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se a coluna 'data_criacao' existe na tabela contatos
SET @exists = 0;
SELECT COUNT(*) INTO @exists FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'contatos' AND column_name = 'data_criacao';

-- Se a coluna não existir, adicioná-la
SET @query = IF(@exists = 0, 
    'ALTER TABLE contatos ADD COLUMN data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP',
    'SELECT "Coluna data_criacao já existe"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
