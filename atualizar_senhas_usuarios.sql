-- Atualizar a senha do administrador para 'admin123'
UPDATE usuarios SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@friocerto.com.br';

-- Atualizar a senha do administrador 2 para 'admin123'
UPDATE usuarios SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'simaorefrigeracao2@gmail.com';

-- Atualizar a senha do técnico Carlos para 'admin123'
UPDATE usuarios SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'carlos@simaorefrigeracao.com.br';

-- Atualizar todas as outras senhas para 'admin123' (caso existam outros usuários)
UPDATE usuarios SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE senha IS NULL OR senha = '';
