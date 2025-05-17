-- Inserir mais clientes
INSERT INTO `clientes` (`nome`, `email`, `telefone`, `endereco`, `cidade`, `estado`, `cep`, `tipo`, `observacoes`, `data_criacao`, `data_atualizacao`) VALUES
('Escola Municipal São José', 'contato@escolasaojose.edu.br', '(85) 3222-1111', 'Rua da Educação, 500', 'Fortaleza', 'CE', '60000-000', 'comercial', 'Cliente desde 2023', NOW(), NULL),
('Hospital Santa Clara', 'adm@hospitalstaclara.com.br', '(85) 3333-2222', 'Av. da Saúde, 1000', 'Fortaleza', 'CE', '60000-000', 'comercial', 'Contrato de manutenção mensal', NOW(), NULL),
('Condomínio Jardim das Flores', 'sindico@jardimdasflores.com.br', '(85) 3444-5555', 'Rua das Acácias, 200', 'Fortaleza', 'CE', '60000-000', 'comercial', 'Manutenção de 20 aparelhos', NOW(), NULL),
('Ana Beatriz Mendes', 'anabeatriz@email.com', '(85) 99888-7777', 'Rua das Palmeiras, 50, Apto 302', 'Fortaleza', 'CE', '60000-000', 'residencial', NULL, NOW(), NULL),
('Carlos Eduardo Ferreira', 'carlos.ferreira@email.com', '(85) 99777-6666', 'Av. Washington Soares, 1000, Casa 5', 'Fortaleza', 'CE', '60000-000', 'residencial', NULL, NOW(), NULL),
('Padaria Pão Dourado', 'contato@paodourado.com.br', '(85) 3555-6666', 'Rua do Comércio, 300', 'Fortaleza', 'CE', '60000-000', 'comercial', 'Câmara fria para confeitaria', NOW(), NULL),
('Farmácia Saúde Total', 'gerencia@saudetotal.com.br', '(85) 3666-7777', 'Av. Santos Dumont, 500', 'Fortaleza', 'CE', '60000-000', 'comercial', NULL, NOW(), NULL),
('Indústria Têxtil Nordeste', 'contato@textilenordeste.com.br', '(85) 3777-8888', 'Distrito Industrial, Lote 15', 'Maracanaú', 'CE', '60000-000', 'industrial', 'Climatização de galpão industrial', NOW(), NULL);

-- Inserir mais técnicos
INSERT INTO `tecnicos` (`nome`, `email`, `telefone`, `especialidade`, `cor`, `status`, `data_criacao`, `data_atualizacao`, `usuario_id`) VALUES
('Roberto Almeida', 'roberto@simaorefrigeracao.com.br', '(85) 98765-4324', 'Instalação e Manutenção', '#8B5CF6', 'ativo', NOW(), NULL, NULL),
('José Carlos Lima', 'jose@simaorefrigeracao.com.br', '(85) 98765-4325', 'Projetos', '#EC4899', 'ativo', NOW(), NULL, NULL),
('Fernando Costa', 'fernando@simaorefrigeracao.com.br', '(85) 98765-4326', 'Manutenção Preventiva', '#14B8A6', 'ativo', NOW(), NULL, NULL);

-- Inserir mais usuários (admin e técnicos)
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `nivel`, `ultimo_login`, `data_criacao`, `data_atualizacao`) VALUES
('Admin Teste', 'admin@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW(), NULL),
('Técnico Teste', 'tecnico@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW(), NOW(), NULL),
('Roberto Almeida', 'roberto@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW(), NOW(), NULL),
('José Carlos Lima', 'jose@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW(), NOW(), NULL),
('Fernando Costa', 'fernando@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', NOW(), NOW(), NULL);

-- Atualizar IDs de usuários para técnicos
UPDATE `tecnicos` SET `usuario_id` = (SELECT `id` FROM `usuarios` WHERE `email` = 'tecnico@simaorefrigeracao.com.br') WHERE `email` = 'carlos@simaorefrigeracao.com.br';
UPDATE `tecnicos` SET `usuario_id` = (SELECT `id` FROM `usuarios` WHERE `email` = 'roberto@simaorefrigeracao.com.br') WHERE `email` = 'roberto@simaorefrigeracao.com.br';
UPDATE `tecnicos` SET `usuario_id` = (SELECT `id` FROM `usuarios` WHERE `email` = 'jose@simaorefrigeracao.com.br') WHERE `email` = 'jose@simaorefrigeracao.com.br';
UPDATE `tecnicos` SET `usuario_id` = (SELECT `id` FROM `usuarios` WHERE `email` = 'fernando@simaorefrigeracao.com.br') WHERE `email` = 'fernando@simaorefrigeracao.com.br';

-- Inserir mais agendamentos (passados, presentes e futuros)
INSERT INTO `agendamentos` (`titulo`, `cliente_id`, `servico_id`, `tecnico_id`, `data_agendamento`, `hora_inicio`, `hora_fim`, `observacoes`, `status`, `data_criacao`, `data_atualizacao`, `valor`, `valor_pendente`, `data_garantia`, `observacoes_tecnicas`, `local_servico`) VALUES
-- Agendamentos passados concluídos
('Instalação Split 12000 BTUs', 3, 1, 1, DATE_SUB(CURDATE(), INTERVAL 15 DAY), '09:00:00', '11:00:00', 'Cliente solicitou instalação com urgência', 'concluido', NOW(), NOW(), 350.00, 0.00, DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 15 DAY), INTERVAL 3 MONTH), 'Instalação realizada com sucesso. Aparelho funcionando normalmente.', 'Residência do cliente'),
('Manutenção Preventiva', 1, 2, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), '14:00:00', '16:00:00', 'Manutenção semestral', 'concluido', NOW(), NOW(), 180.00, 0.00, DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 10 DAY), INTERVAL 3 MONTH), 'Limpeza de filtros e verificação de gás. Sistema funcionando normalmente.', 'Estabelecimento comercial'),
('Reparo em Câmara Fria', 4, 5, 3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), '08:00:00', '12:00:00', 'Câmara não está refrigerando adequadamente', 'concluido', NOW(), NOW(), 850.00, 250.00, DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 7 DAY), INTERVAL 3 MONTH), 'Substituição de compressor e recarga de gás. Funcionamento normalizado.', 'Indústria'),

-- Agendamentos passados cancelados
('Visita Técnica', 2, 4, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), '10:00:00', '11:00:00', 'Verificar problema de vazamento', 'cancelado', NOW(), NOW(), 0.00, 0.00, NULL, 'Cliente cancelou por indisponibilidade', NULL),

-- Agendamentos para hoje
('Instalação Split 18000 BTUs', 5, 1, 1, CURDATE(), '09:00:00', '12:00:00', 'Instalação em quarto principal', 'pendente', NOW(), NULL, 0.00, 0.00, NULL, NULL, 'Residência do cliente'),
('Manutenção Corretiva', 6, 3, 2, CURDATE(), '14:00:00', '16:00:00', 'Ar condicionado não está refrigerando', 'pendente', NOW(), NULL, 0.00, 0.00, NULL, NULL, 'Estabelecimento comercial'),

-- Agendamentos futuros
('Instalação de 5 aparelhos', 7, 1, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00:00', '18:00:00', 'Instalação em farmácia nova', 'pendente', NOW(), NULL, 0.00, 0.00, NULL, NULL, 'Estabelecimento comercial'),
('Manutenção Preventiva', 8, 2, 3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '12:00:00', 'Manutenção trimestral', 'pendente', NOW(), NULL, 0.00, 0.00, NULL, NULL, 'Indústria'),
('Projeto de Climatização', 3, 6, 4, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:00:00', '16:00:00', 'Projeto para nova residência', 'pendente', NOW(), NULL, 0.00, 0.00, NULL, NULL, 'Escritório'),
('Instalação Split 9000 BTUs', 5, 1, 2, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:00:00', '12:00:00', 'Instalação em quarto de hóspedes', 'pendente', NOW(), NULL, 0.00, 0.00, NULL, NULL, 'Residência do cliente'),
('Manutenção Corretiva', 1, 3, 3, DATE_ADD(CURDATE(), INTERVAL 10 DAY), '13:00:00', '15:00:00', 'Verificar ruído anormal', 'pendente', NOW(), NULL, 0.00, 0.00, NULL, NULL, 'Estabelecimento comercial');

-- Inserir dados na tabela historico_servicos para agendamentos concluídos
INSERT INTO `historico_servicos` (`agendamento_id`, `cliente_id`, `tecnico_id`, `servico_id`, `data_servico`, `hora_inicio`, `hora_fim`, `valor`, `valor_pendente`, `status`, `observacoes`, `observacoes_tecnicas`, `local_servico`, `data_garantia`, `data_criacao`)
SELECT `id`, `cliente_id`, `tecnico_id`, `servico_id`, `data_agendamento`, `hora_inicio`, `hora_fim`, `valor`, `valor_pendente`, `status`, `observacoes`, `observacoes_tecnicas`, `local_servico`, `data_garantia`, NOW()
FROM `agendamentos`
WHERE `status` = 'concluido';

-- Inserir contatos
INSERT INTO `contatos` (`nome`, `email`, `telefone`, `servico_id`, `mensagem`, `status`, `data_criacao`, `data_atualizacao`) VALUES
('Marcos Paulo', 'marcos@email.com', '(85) 99999-8888', 1, 'Gostaria de um orçamento para instalação de ar condicionado em minha residência.', 'novo', NOW(), NULL),
('Empresa ABC', 'contato@empresaabc.com.br', '(85) 3333-4444', 2, 'Precisamos de manutenção preventiva em nossos equipamentos. São 10 aparelhos split.', 'novo', NOW(), NULL),
('Juliana Silva', 'juliana@email.com', '(85) 99888-7777', 4, 'Meu ar condicionado está com problema. Preciso de uma visita técnica urgente.', 'novo', NOW(), NULL);