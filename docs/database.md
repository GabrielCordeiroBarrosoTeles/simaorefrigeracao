# Database Schema

## Tabelas

### clientes
- `id` - Primary Key
- `nome` - Nome do cliente
- `email` - Email único
- `telefone` - Telefone formatado
- `endereco` - Endereço completo
- `cidade` - Cidade
- `estado` - Estado (2 chars)
- `cep` - CEP formatado
- `tipo` - ENUM(residencial, comercial, industrial)
- `observacoes` - Observações gerais
- `data_criacao` - Timestamp de criação
- `data_atualizacao` - Timestamp de atualização

### servicos
- `id` - Primary Key
- `titulo` - Nome do serviço
- `icone` - Ícone para interface
- `descricao` - Descrição detalhada
- `itens` - JSON com lista de itens
- `garantia_meses` - Período de garantia
- `data_criacao` - Timestamp de criação
- `data_atualizacao` - Timestamp de atualização

### tecnicos
- `id` - Primary Key
- `nome` - Nome do técnico
- `email` - Email único
- `telefone` - Telefone formatado
- `especialidade` - Área de especialização
- `cor` - Cor para calendário (hex)
- `status` - ENUM(ativo, inativo)
- `usuario_id` - FK para usuarios
- `data_criacao` - Timestamp de criação
- `data_atualizacao` - Timestamp de atualização

### agendamentos
- `id` - Primary Key
- `titulo` - Título do agendamento
- `cliente_id` - FK para clientes
- `servico_id` - FK para servicos
- `tecnico_id` - FK para tecnicos
- `data_agendamento` - Data do serviço
- `hora_inicio` - Hora de início
- `hora_fim` - Hora de término
- `observacoes` - Observações gerais
- `status` - ENUM(pendente, concluido, cancelado)
- `valor` - Valor do serviço
- `valor_pendente` - Valor pendente
- `data_garantia` - Data limite da garantia
- `observacoes_tecnicas` - Observações técnicas
- `local_servico` - Local específico do serviço
- `data_criacao` - Timestamp de criação
- `data_atualizacao` - Timestamp de atualização

### usuarios
- `id` - Primary Key
- `nome` - Nome do usuário
- `email` - Email único
- `senha` - Hash da senha
- `nivel` - ENUM(admin, editor, tecnico, tecnico_adm)
- `ultimo_login` - Timestamp do último login
- `data_criacao` - Timestamp de criação
- `data_atualizacao` - Timestamp de atualização