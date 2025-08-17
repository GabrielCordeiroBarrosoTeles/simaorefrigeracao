# Arquitetura do Sistema

## Domain-Driven Design (DDD)

### Camadas

#### Domain Layer
- **Entities**: Objetos de negócio com identidade
- **Value Objects**: Objetos imutáveis sem identidade
- **Repositories**: Interfaces para persistência
- **Services**: Lógica de domínio complexa
- **Exceptions**: Exceções específicas do domínio

#### Application Layer
- **Services**: Orquestração de casos de uso
- **DTOs**: Objetos de transferência de dados
- **Events**: Eventos de aplicação
- **Factories**: Criação de objetos complexos

#### Infrastructure Layer
- **Repositories**: Implementações concretas
- **Factories**: Criação de recursos externos
- **Commands**: Comandos CLI
- **Fixtures**: Dados de teste

#### Presentation Layer
- **Controllers**: Pontos de entrada HTTP
- **Views**: Templates de apresentação

## Design Patterns

### Repository Pattern
Abstração da camada de persistência através de interfaces.

### Factory Pattern
Centralização da criação de objetos complexos.

### Value Object Pattern
Encapsulamento de dados com validação.

### Observer Pattern
Sistema de eventos para desacoplamento.

### DTO Pattern
Transferência de dados entre camadas.