-- Script para atualizar as configurações da empresa no banco de dados
UPDATE configuracoes 
SET 
    nome_empresa = 'Simão Refrigeração',
    email = 'simaorefrigeracao2@gmail.com',
    telefone = '(85) 98810-6463',
    endereco = 'Av. Sabino Monte, 3878 - São João do Tauape, Fortaleza - CE',
    data_atualizacao = NOW()
WHERE id = 1;

-- Atualizar o email do administrador (opcional)
UPDATE usuarios
SET 
    email = 'simaorefrigeracao2@gmail.com'
WHERE nivel = 'admin' AND id = 1;
