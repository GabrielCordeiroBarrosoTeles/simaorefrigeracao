# Sistema Simão Refrigeração - Arquitetura Refatorada

## 🚀 Instalação Rápida

```bash
# Clonar e configurar
git clone <repo>
cd simaorefrigeracao
make setup
make install

# Configurar banco
make db-create
make db-fixtures

# Iniciar servidor
make serve
```

## 📋 Comandos Disponíveis

```bash
make help              # Lista todos os comandos
make install           # Instala dependências
make db-create         # Cria banco de dados
make db-fixtures       # Carrega dados fictícios
make db-reset          # Reseta banco completo
make serve             # Servidor desenvolvimento
make test              # Executa testes
make clean             # Limpa cache/logs
```

## 🏗️ Entidades Criadas

### Cliente
- Value Objects: Email, Telefone
- Enum: ClienteTipo (residencial, comercial, industrial)
- Validações automáticas

### Agendamento
- Relacionamentos: Cliente, Servico, Tecnico
- Enum: AgendamentoStatus (pendente, concluido, cancelado)
- Campos: valor, garantia, observações técnicas

### Servico
- JSON para itens do serviço
- Garantia em meses
- Ícone e descrição

### Tecnico
- Value Objects: Email, Telefone
- Enum: TecnicoStatus (ativo, inativo)
- Especialidade e cor para calendário

### Usuario
- Enum: UsuarioNivel (admin, editor, tecnico, tecnico_adm)
- Hash de senha automático
- Controle de último login

## 🎯 Fixtures (Dados Fictícios)

### ClienteFixtures
- 5 clientes de exemplo
- Diferentes tipos (residencial, comercial, industrial)
- Dados realistas

### ServicoFixtures
- 4 serviços principais
- Ícones e descrições
- Diferentes períodos de garantia

### TecnicoFixtures
- 4 técnicos especializados
- Cores diferentes para calendário
- Especialidades variadas

## 🛠️ Comandos Doctrine

```bash
# Schema
bin/doctrine orm:schema-tool:create
bin/doctrine orm:schema-tool:update --force
bin/doctrine orm:validate-schema

# Fixtures
bin/console fixtures:load

# Migrations
bin/doctrine migrations:generate
bin/doctrine migrations:migrate
```

## 📁 Estrutura Atualizada

```
src/
├── Domain/
│   ├── Entity/           # Todas as entidades do SQL
│   ├── ValueObject/      # Email, Telefone
│   ├── Repository/       # Interfaces
│   ├── Service/          # Regras de negócio
│   └── Exception/        # Exceções tipadas
├── Infrastructure/
│   ├── Repository/       # Implementações Doctrine
│   ├── Fixtures/         # Dados fictícios
│   ├── Command/          # Comandos CLI
│   └── Factory/          # Factories
├── Application/
│   ├── Service/          # Orquestração
│   ├── DTO/              # Transfer Objects
│   ├── Factory/          # Conversores
│   └── Event/            # Eventos
└── Presentation/
    └── Controller/       # APIs REST
```

## 🎨 Design Patterns Aplicados

- **Repository Pattern**: Abstração de persistência
- **Factory Pattern**: Criação de objetos
- **Value Object Pattern**: Email, Telefone
- **Strategy Pattern**: Enums para comportamentos
- **Observer Pattern**: Sistema de eventos
- **DTO Pattern**: Transferência de dados

## ✅ Princípios SOLID

- **S**: Uma responsabilidade por classe
- **O**: Extensível via interfaces
- **L**: Substituição de implementações
- **I**: Interfaces específicas
- **D**: Inversão de dependências

## 🔧 Próximos Passos

1. **Testes**: PHPUnit para todas as camadas
2. **API**: Endpoints REST completos
3. **Autenticação**: Symfony Security
4. **Cache**: Redis/Memcached
5. **Logs**: Monolog estruturado
6. **Docker**: Containerização completa