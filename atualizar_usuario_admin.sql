-- Atualizar o usuário administrador para garantir que as credenciais estejam corretas
-- A senha 'admin123' tem o hash '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'

-- Verificar se o usuário admin@friocerto.com.br existe
UPDATE usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    nivel = 'admin'
WHERE email = 'admin@friocerto.com.br';

-- Se o usuário não existir, criar um novo
INSERT INTO usuarios (nome, email, senha, nivel, data_criacao)
SELECT 'Administrador', 'admin@friocerto.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@friocerto.com.br');
