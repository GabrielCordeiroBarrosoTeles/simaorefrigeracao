-- Verificar se a coluna 'data_cadastro' existe na tabela clientes
SET @exists = 0;
SELECT COUNT(*) INTO @exists FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'clientes' AND column_name = 'data_cadastro';

-- Se a coluna não existir, adicioná-la
SET @query = IF(@exists = 0, 
    'ALTER TABLE clientes ADD COLUMN data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP',
    'SELECT "Coluna data_cadastro já existe"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se a coluna 'data_criacao' existe na tabela clientes
SET @exists = 0;
SELECT COUNT(*) INTO @exists FROM information_schema.columns 
WHERE table_schema = DATABASE() AND table_name = 'clientes' AND column_name = 'data_criacao';

-- Se a coluna não existir, adicioná-la
SET @query = IF(@exists = 0, 
    'ALTER TABLE clientes ADD COLUMN data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP',
    'SELECT "Coluna data_criacao já existe"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
