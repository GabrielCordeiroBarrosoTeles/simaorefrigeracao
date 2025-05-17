-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 06:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simaorefrigeracao`
--

-- --------------------------------------------------------

--
-- Table structure for table `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `tecnico_id` int(11) NOT NULL,
  `data_agendamento` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('pendente','concluido','cancelado') NOT NULL DEFAULT 'pendente',
  `data_criacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT 0.00 COMMENT 'Valor cobrado pelo serviço',
  `valor_pendente` decimal(10,2) DEFAULT 0.00 COMMENT 'Valor pendente de pagamento',
  `data_garantia` date DEFAULT NULL COMMENT 'Data de término da garantia',
  `observacoes_tecnicas` text DEFAULT NULL COMMENT 'Observações técnicas do serviço realizado',
  `local_servico` varchar(255) DEFAULT NULL COMMENT 'Local onde o serviço foi realizado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `titulo`, `cliente_id`, `servico_id`, `tecnico_id`, `data_agendamento`, `hora_inicio`, `hora_fim`, `observacoes`, `status`, `data_criacao`, `data_atualizacao`, `valor`, `valor_pendente`, `data_garantia`, `observacoes_tecnicas`, `local_servico`) VALUES
(2, 'Manutenção Preventiva', 1, 2, 2, '2025-04-23', '14:00:00', '16:00:00', NULL, 'pendente', '2025-04-23 22:07:04', NULL, 0.00, 0.00, NULL, NULL, NULL),
(3, 'Instalação de Câmara Fria', 4, 5, 3, '2025-04-25', '08:00:00', '17:00:00', NULL, 'pendente', '2025-04-23 22:07:04', NULL, 0.00, 0.00, NULL, NULL, NULL),
(4, 'Manutenção Corretiva', 2, 3, 2, '2025-04-24', '10:00:00', '12:00:00', NULL, 'pendente', '2025-04-23 22:07:04', NULL, 0.00, 0.00, NULL, NULL, NULL),
(5, 'Instalação de Ar Condicionado', 1, 1, 1, '2025-04-25', '10:00:00', '12:00:00', 'Cliente solicitou instalação com urgência', 'pendente', '0000-00-00 00:00:00', NULL, 0.00, 0.00, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `tipo` enum('residencial','comercial','industrial') NOT NULL DEFAULT 'residencial',
  `observacoes` text DEFAULT NULL,
  `data_criacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`, `endereco`, `cidade`, `estado`, `cep`, `tipo`, `observacoes`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'Supermercado Bom Preço', 'contato@bompreco.com.br', '(85) 3333-4444', 'Av. Comercial, 1000', 'Fortaleza', 'CE', NULL, 'comercial', NULL, '2025-04-23 22:07:04', NULL),
(2, 'Restaurante Sabor & Arte', 'contato@saborarte.com.br', '(85) 3333-5555', 'Rua dos Restaurantes, 500', 'Fortaleza', 'CE', NULL, 'comercial', NULL, '2025-04-23 22:07:04', NULL),
(3, 'João Pereira', 'joao@email.com', '(85) 99999-8888', 'Rua Residencial, 100, Apto 50', 'Fortaleza', 'CE', NULL, 'residencial', NULL, '2025-04-23 22:07:04', NULL),
(4, 'Indústria Alimentos ABC', 'contato@alimentosabc.com.br', '(85) 3333-6666', 'Rodovia Industrial, Km 10', 'Caucaia', 'CE', NULL, 'industrial', NULL, '2025-04-23 22:07:04', NULL),
(5, 'João Silva', 'joao@example.com', '(11) 98765-4321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 'residencial', NULL, '0000-00-00 00:00:00', NULL),
(6, 'Maria Oliveira', 'maria@example.com', '(11) 91234-5678', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', 'residencial', NULL, '0000-00-00 00:00:00', NULL),
(7, 'Pedro Santos', 'pedro@example.com', '(11) 99876-5432', 'Rua Augusta, 500', 'São Paulo', 'SP', '01305-000', 'residencial', NULL, '0000-00-00 00:00:00', NULL),
(11, 'João Silva', 'joao.silva@email.com', '(11) 98765-4321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 'residencial', 'Cliente preferencial', '0000-00-00 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL DEFAULT 1,
  `nome_empresa` varchar(100) NOT NULL,
  `descricao_empresa` text DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `titulo_hero` varchar(100) DEFAULT NULL,
  `subtitulo_hero` text DEFAULT NULL,
  `imagem_hero` varchar(255) DEFAULT NULL,
  `imagem_sobre` varchar(255) DEFAULT NULL,
  `link_sobre` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `data_atualizacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `nome_empresa`, `descricao_empresa`, `telefone`, `email`, `endereco`, `titulo_hero`, `subtitulo_hero`, `imagem_hero`, `imagem_sobre`, `link_sobre`, `facebook`, `instagram`, `linkedin`, `whatsapp`, `data_atualizacao`) VALUES
(1, 'Simão Refrigeração', 'Somos uma empresa especializada em soluções de climatização, com anos de experiência no mercado. Nossa equipe é formada por profissionais qualificados e comprometidos com a excelência.', '(85) 98810-6463', 'simaorefrigeracao2@gmail.com', 'Av. Sabino Monte, 3878 - São João do Tauape, Fortaleza - CE', 'Soluções completas em ar condicionado', 'Oferecemos serviços de instalação, manutenção e projetos para garantir o conforto térmico ideal para sua casa ou empresa.', NULL, NULL, NULL, '', '', '', '', '2025-05-05 02:13:08');

-- --------------------------------------------------------

--
-- Table structure for table `contatos`
--

CREATE TABLE `contatos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `servico_id` int(11) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `status` enum('novo','em_andamento','respondido','finalizado') NOT NULL DEFAULT 'novo',
  `data_criacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `depoimentos`
--

CREATE TABLE `depoimentos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `texto` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_criacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `depoimentos`
--

INSERT INTO `depoimentos` (`id`, `nome`, `tipo`, `texto`, `foto`, `ativo`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'João Carlos', 'Residencial', 'Excelente serviço! A equipe foi pontual, profissional e deixou tudo limpo após a instalação. O ar condicionado está funcionando perfeitamente.', NULL, 1, '2025-04-23 22:07:03', NULL),
(2, 'Maria Silva', 'Comercial', 'Contratamos para a manutenção dos equipamentos da nossa loja e o resultado foi excelente. Recomendo para todos que precisam de serviços de qualidade.', NULL, 1, '2025-04-23 22:07:03', NULL),
(3, 'Roberto Lima', 'Industrial', 'Projeto de climatização para nossa fábrica executado com perfeição. Equipe técnica altamente qualificada e comprometida com prazos.', NULL, 1, '2025-04-23 22:07:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `estatisticas`
--

CREATE TABLE `estatisticas` (
  `id` int(11) NOT NULL,
  `valor` varchar(20) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `estatisticas`
--

INSERT INTO `estatisticas` (`id`, `valor`, `descricao`, `ordem`) VALUES
(1, '10+', 'Anos de experiência', 1),
(2, '500+', 'Clientes satisfeitos', 2),
(3, '1000+', 'Projetos realizados', 3),
(4, '24h', 'Atendimento', 4);

-- --------------------------------------------------------

--
-- Table structure for table `historico_servicos`
--

CREATE TABLE `historico_servicos` (
  `id` int(11) NOT NULL,
  `agendamento_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `tecnico_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `data_servico` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_pendente` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pendente','concluido','cancelado') NOT NULL DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  `observacoes_tecnicas` text DEFAULT NULL,
  `local_servico` varchar(255) DEFAULT NULL,
  `data_garantia` date DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicos`
--

CREATE TABLE `servicos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `descricao` text NOT NULL,
  `itens` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`itens`)),
  `data_criacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `garantia_meses` int(11) DEFAULT 3 COMMENT 'Duração da garantia em meses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servicos`
--

INSERT INTO `servicos` (`id`, `titulo`, `icone`, `descricao`, `itens`, `data_criacao`, `data_atualizacao`, `garantia_meses`) VALUES
(1, 'Instalação de Ar Condicionado', 'fan', 'Instalação profissional de equipamentos residenciais e comerciais.', '[\"Instalação de splits e multi-splits\", \"Instalação de ar condicionado central\", \"Instalação de VRF/VRV\"]', '2025-04-23 22:07:03', NULL, 3),
(2, 'Manutenção Preventiva', 'thermometer', 'Serviços regulares para garantir o funcionamento ideal do seu equipamento.', '[\"Limpeza de filtros e componentes\", \"Verificação de gás refrigerante\", \"Inspeção de componentes elétricos\"]', '2025-04-23 22:07:03', NULL, 3),
(3, 'Manutenção Corretiva', 'tools', 'Reparo rápido e eficiente para resolver problemas no seu equipamento.', '[\"Diagnóstico preciso de falhas\", \"Reparo de vazamentos\", \"Substituição de componentes\"]', '2025-04-23 22:07:03', NULL, 3),
(4, 'Visita Técnica', 'phone', 'Avaliação profissional para identificar problemas e propor soluções.', '[\"Diagnóstico de problemas\", \"Orçamento detalhado\", \"Recomendações técnicas\"]', '2025-04-23 22:07:03', NULL, 3),
(5, 'Câmara Frigorífica', 'snowflake', 'Soluções para armazenamento refrigerado comercial e industrial.', '[\"Instalação de câmaras frigoríficas\", \"Manutenção de sistemas de refrigeração\", \"Projetos personalizados\"]', '2025-04-23 22:07:03', NULL, 3),
(6, 'Projetos', 'file-text', 'Desenvolvimento de projetos de climatização para diversos ambientes.', '[\"Projetos para residências\", \"Projetos para comércios\", \"Projetos para indústrias\"]', '2025-04-23 22:07:03', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tecnicos`
--

CREATE TABLE `tecnicos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `especialidade` varchar(100) DEFAULT NULL,
  `cor` varchar(7) DEFAULT '#3b82f6',
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `data_criacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tecnicos`
--

INSERT INTO `tecnicos` (`id`, `nome`, `email`, `telefone`, `especialidade`, `cor`, `status`, `data_criacao`, `data_atualizacao`, `usuario_id`) VALUES
(1, 'Carlos Silva', 'carlos@simaorefrigeracao.com.br', '(85) 98765-4321', 'Instalação', '#3b82f6', 'ativo', '2025-04-23 22:07:04', NULL, NULL),
(2, 'Marcos Oliveira', 'marcos@simaorefrigeracao.com.br', '(85) 98765-4322', 'Manutenção', '#10b981', 'ativo', '2025-04-23 22:07:04', NULL, NULL),
(3, 'Pedro Santos', 'pedro@simaorefrigeracao.com.br', '(85) 98765-4323', 'Câmaras Frigoríficas', '#f59e0b', 'ativo', '2025-04-23 22:07:04', NULL, NULL),
(4, 'Carlos Oliveira', 'carlos.oliveira@email.com', '(11) 91234-5678', 'Instalação e Manutenção', '#3b82f6', 'ativo', '0000-00-00 00:00:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` enum('admin','editor','tecnico','tecnico_adm') NOT NULL DEFAULT 'editor',
  `ultimo_login` datetime DEFAULT NULL,
  `data_criacao` datetime NOT NULL,
  `data_atualizacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel`, `ultimo_login`, `data_criacao`, `data_atualizacao`) VALUES
(1, 'Administrador', 'simaorefrigeracao2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, '2025-04-23 22:07:03', NULL),
(3, 'Carlos', 'carlos@simaorefrigeracao.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tecnico', '2025-04-27 00:32:04', '2025-04-24 23:01:53', NULL),
(5, 'Administrador', 'admin@friocerto.com.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-05-17 13:47:51', '0000-00-00 00:00:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agendamentos_cliente` (`cliente_id`),
  ADD KEY `idx_agendamentos_tecnico` (`tecnico_id`),
  ADD KEY `idx_agendamentos_servico` (`servico_id`),
  ADD KEY `idx_agendamentos_data` (`data_agendamento`),
  ADD KEY `idx_agendamentos_status` (`status`);

--
-- Indexes for table `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contatos`
--
ALTER TABLE `contatos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servico_id` (`servico_id`);

--
-- Indexes for table `depoimentos`
--
ALTER TABLE `depoimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estatisticas`
--
ALTER TABLE `estatisticas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `historico_servicos`
--
ALTER TABLE `historico_servicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agendamento_id` (`agendamento_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `tecnico_id` (`tecnico_id`),
  ADD KEY `servico_id` (`servico_id`);

--
-- Indexes for table `servicos`
--
ALTER TABLE `servicos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tecnicos`
--
ALTER TABLE `tecnicos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_tecnico_usuario` (`usuario_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contatos`
--
ALTER TABLE `contatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `depoimentos`
--
ALTER TABLE `depoimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `estatisticas`
--
ALTER TABLE `estatisticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `historico_servicos`
--
ALTER TABLE `historico_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servicos`
--
ALTER TABLE `servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tecnicos`
--
ALTER TABLE `tecnicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_3` FOREIGN KEY (`tecnico_id`) REFERENCES `tecnicos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contatos`
--
ALTER TABLE `contatos`
  ADD CONSTRAINT `contatos_ibfk_1` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `historico_servicos`
--
ALTER TABLE `historico_servicos`
  ADD CONSTRAINT `historico_servicos_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_servicos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_servicos_ibfk_3` FOREIGN KEY (`tecnico_id`) REFERENCES `tecnicos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_servicos_ibfk_4` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tecnicos`
--
ALTER TABLE `tecnicos`
  ADD CONSTRAINT `fk_tecnico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
