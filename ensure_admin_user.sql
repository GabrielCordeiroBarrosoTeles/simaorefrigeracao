-- Verificar se a tabela de usuários existe
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
    ultimo_login DATETIME,
    remember_token VARCHAR(255),
    token_expiry BIGINT,
    data_criacao DATETIME NOT NULL,
    data_atualizacao DATETIME
);

-- Inserir ou atualizar o usuário administrador
-- Senha: admin123 (hash gerado com password_hash)
INSERT INTO usuarios (nome, email, senha, nivel, data_criacao)
VALUES 
('Administrador', 'admin@friocerto.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW())
ON DUPLICATE KEY UPDATE 
    senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    nivel = 'admin';

-- Verificar se o usuário foi criado
SELECT * FROM usuarios WHERE email = 'admin@friocerto.com.br';
