# Arquitetura Refatorada - Sistema Simão Refrigeração

## Visão Geral

Sistema refatorado aplicando **Design Patterns**, **Princípios SOLID** e **Symfony Components** com **Doctrine ORM**.

## Arquitetura Implementada

### 1. Domain-Driven Design (DDD)
```
src/
├── Domain/                 # Camada de Domínio
│   ├── Entity/            # Entidades de negócio
│   ├── ValueObject/       # Objetos de valor
│   ├── Repository/        # Interfaces de repositório
│   ├── Service/           # Serviços de domínio
│   └── Exception/         # Exceções de domínio
├── Application/           # Camada de Aplicação
│   ├── Service/          # Serviços de aplicação
│   ├── DTO/              # Data Transfer Objects
│   ├── Factory/          # Factories
│   └── Event/            # Eventos de aplicação
├── Infrastructure/       # Camada de Infraestrutura
│   ├── Repository/       # Implementações de repositório
│   ├── Factory/          # Factories de infraestrutura
│   └── Event/            # Event dispatcher
└── Presentation/         # Camada de Apresentação
    └── Controller/       # Controllers REST/Web
```

## Design Patterns Implementados

### 1. Repository Pattern
- **Interface**: `ClienteRepositoryInterface`
- **Implementação**: `DoctrineClienteRepository`
- **Benefício**: Abstração da persistência

### 2. Factory Pattern
- **ClienteFactory**: Conversão entre DTO e Entity
- **EntityManagerFactory**: Criação do EntityManager
- **Benefício**: Centralização da criação de objetos

### 3. Strategy Pattern (Value Objects)
- **Email**: Validação e formatação de email
- **Telefone**: Validação e formatação de telefone
- **Benefício**: Encapsulamento de regras específicas

### 4. Observer Pattern (Events)
- **EventDispatcher**: Gerenciamento de eventos
- **ClienteCriadoEvent**: Evento de cliente criado
- **Benefício**: Desacoplamento de ações secundárias

### 5. DTO Pattern
- **ClienteDTO**: Transferência de dados
- **Benefício**: Isolamento da camada de apresentação

## Princípios SOLID Aplicados

### S - Single Responsibility Principle
- **ClienteService**: Apenas regras de negócio de cliente
- **ValidationService**: Apenas validação
- **ClienteRepository**: Apenas persistência

### O - Open/Closed Principle
- **Interfaces de repositório**: Extensíveis sem modificação
- **Value Objects**: Novos tipos sem alterar existentes

### L - Liskov Substitution Principle
- **Implementações de repositório**: Substituíveis pelas interfaces
- **Events**: Implementam EventInterface

### I - Interface Segregation Principle
- **Interfaces específicas**: ClienteRepositoryInterface focada
- **EventInterface**: Mínima e coesa

### D - Dependency Inversion Principle
- **Injeção de dependência**: Via Symfony DI Container
- **Abstrações**: Dependência de interfaces, não implementações

## Componentes Symfony Utilizados

### 1. Dependency Injection
```yaml
# config/services.yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
```

### 2. Validator
```php
#[Assert\NotBlank]
#[Assert\Length(min: 2, max: 100)]
private string $nome;
```

### 3. HTTP Foundation
```php
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
```

## Doctrine ORM Features

### 1. Attribute Mapping
```php
#[ORM\Entity]
#[ORM\Table(name: 'clientes')]
class Cliente
```

### 2. Embedded Objects
```php
#[ORM\Embedded(class: Email::class)]
private Email $email;
```

### 3. Lifecycle Callbacks
```php
#[ORM\PreUpdate]
public function onPreUpdate(): void
```

### 4. Enums
```php
#[ORM\Column(type: 'string', enumType: ClienteTipo::class)]
private ClienteTipo $tipo;
```

## Vantagens da Nova Arquitetura

### 1. Testabilidade
- **Mocks**: Interfaces permitem mocking fácil
- **Isolamento**: Cada camada testável independentemente

### 2. Manutenibilidade
- **Separação clara**: Responsabilidades bem definidas
- **Baixo acoplamento**: Mudanças isoladas

### 3. Extensibilidade
- **Novos repositórios**: Implementar interfaces
- **Novos eventos**: Adicionar listeners
- **Novos value objects**: Sem impacto em código existente

### 4. Performance
- **Doctrine ORM**: Query optimization
- **Lazy loading**: Carregamento sob demanda
- **Cache**: Symfony Cache component

### 5. Robustez
- **Validação**: Symfony Validator
- **Exceções tipadas**: Tratamento específico
- **Value Objects**: Dados sempre válidos

## Como Usar

### 1. Instalação
```bash
composer install
cp .env.example .env
# Configure as variáveis de ambiente
```

### 2. Banco de Dados
```bash
php bin/doctrine orm:schema-tool:create
php bin/doctrine migrations:migrate
```

### 3. Uso da API
```bash
# Criar cliente
POST /api/clientes
{
  "nome": "João Silva",
  "email": "joao@email.com",
  "telefone": "11999999999",
  "tipo": "residencial"
}

# Listar clientes
GET /api/clientes

# Buscar por ID
GET /api/clientes/1

# Atualizar cliente
PUT /api/clientes/1

# Excluir cliente
DELETE /api/clientes/1
```

## Próximos Passos

1. **Implementar outras entidades** (Agendamento, Serviço, Técnico)
2. **Adicionar autenticação** (Symfony Security)
3. **Implementar cache** (Redis/Memcached)
4. **Adicionar logs** (Monolog)
5. **Testes automatizados** (PHPUnit)
6. **API Documentation** (OpenAPI/Swagger)