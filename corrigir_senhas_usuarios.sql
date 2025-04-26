-- Primeiro, vamos verificar se os usuários existem
SELECT * FROM usuarios;

-- Agora, vamos atualizar as senhas para 'admin123'
-- O hash abaixo é o hash bcrypt para 'admin123'
UPDATE usuarios SET 
    senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE 
    email IN ('admin@friocerto.com.br', 'simaorefrigeracao2@gmail.com', 'carlos@simaorefrigeracao.com.br');

-- Se os usuários não existirem, vamos criá-los
INSERT INTO usuarios (nome, email, senha, nivel, ultimo_login)
SELECT 'Administrador', 'admin@friocerto.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'admin@friocerto.com.br');

INSERT INTO usuarios (nome, email, senha, nivel, ultimo_login)
SELECT 'Administrador 2', 'simaorefrigeracao2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'simaorefrigeracao2@gmail.com');

INSERT INTO usuarios (nome, email, senha, nivel, ultimo_login)
SELECT 'Carlos', 'carlos@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW()
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'carlos@simaorefrigeracao.com.br');
