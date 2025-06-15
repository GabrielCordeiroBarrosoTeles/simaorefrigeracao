# Arquitetura do Sistema Simão Refrigeração

## Visão Geral
O sistema Simão Refrigeração é uma aplicação web PHP para gerenciamento de serviços de manutenção de ar-condicionado, seguindo os princípios da Arquitetura Limpa (Clean Architecture) para garantir separação de responsabilidades, testabilidade e manutenibilidade.

## Princípios Arquiteturais

- **Separação de Responsabilidades**: Cada camada tem uma responsabilidade específica
- **Regra de Dependência**: As dependências apontam para dentro (camadas externas dependem de camadas internas)
- **Inversão de Dependência**: Abstrações não dependem de detalhes, detalhes dependem de abstrações
- **Entidades Isoladas**: As entidades de domínio são independentes de frameworks e detalhes de implementação

## Camadas da Arquitetura

### 1. Domain (Núcleo)
Contém as regras de negócio e entidades principais, independentes de qualquer framework ou detalhe de implementação.

- **Entity**: Classes que representam as entidades do domínio (Cliente, Agendamento, Técnico, etc.)
- **Repository Interface**: Interfaces para acesso a dados
- **Domain Service**: Serviços específicos do domínio
- **Value Object**: Objetos de valor imutáveis
- **Exception**: Exceções específicas do domínio

### 2. Application (Casos de Uso)
Orquestra o fluxo de dados entre as camadas, implementando os casos de uso da aplicação.

- **Use Case**: Implementa casos de uso específicos da aplicação
- **Service**: Serviços de aplicação que coordenam múltiplas operações
- **Validator**: Validação de dados de entrada
- **DTO**: Objetos de transferência de dados entre camadas

### 3. Infrastructure (Detalhes Técnicos)
Implementações técnicas e detalhes externos como banco de dados, frameworks e bibliotecas.

- **Persistence**: Implementações concretas dos repositórios
- **Http**: Componentes relacionados a HTTP
- **Config**: Configurações do sistema
- **Migrations**: Migrações do banco de dados

### 4. Presentation (Interface)
Interface com o usuário, seja web, API ou linha de comando.

- **Controller**: Controladores que recebem requisições e delegam para casos de uso
- **View**: Templates e componentes visuais
- **API**: Endpoints da API REST

## Estrutura de Diretórios

```
simaorefrigeracao/
├── src/                           # Código fonte da aplicação
│   ├── Domain/                    # Camada de domínio
│   │   ├── Entity/                # Entidades de domínio
│   │   ├── Repository/            # Interfaces de repositório
│   │   ├── Service/               # Serviços de domínio
│   │   ├── ValueObject/           # Objetos de valor
│   │   └── Exception/             # Exceções de domínio
│   ├── Application/               # Camada de aplicação
│   │   ├── UseCase/               # Casos de uso
│   │   ├── Service/               # Serviços de aplicação
│   │   ├── Validator/             # Validadores
│   │   └── DTO/                   # Objetos de transferência de dados
│   ├── Infrastructure/            # Camada de infraestrutura
│   │   ├── Persistence/           # Implementações de repositórios
│   │   │   └── Migrations/        # Migrações do banco de dados
│   │   ├── Http/                  # Componentes HTTP
│   │   └── Config/                # Configurações
│   └── Presentation/              # Camada de apresentação
│       ├── Controller/            # Controladores
│       ├── View/                  # Views
│       └── Api/                   # Endpoints da API
├── public/                        # Ponto de entrada da aplicação
│   ├── index.php                  # Front controller
│   ├── assets/                    # Recursos estáticos
│   └── uploads/                   # Arquivos enviados pelos usuários
├── bin/                           # Scripts executáveis
├── config/                        # Arquivos de configuração
├── docker/                        # Configurações do Docker
├── vendor/                        # Dependências (gerenciadas pelo Composer)
└── tests/                         # Testes automatizados
```

## Fluxo de Dados

1. **Requisição HTTP**: O usuário faz uma requisição para o sistema
2. **Front Controller**: Recebe a requisição e a encaminha para o controlador apropriado
3. **Controller**: Processa a requisição, converte para DTO e chama o caso de uso apropriado
4. **Use Case**: Implementa a lógica de negócio, utilizando repositórios e serviços
5. **Repository**: Acessa o banco de dados através da camada de persistência
6. **Response**: O resultado é retornado ao usuário, seja como HTML ou JSON

## Padrões Utilizados

- **Repository Pattern**: Para abstrair o acesso a dados
- **DTO (Data Transfer Object)**: Para transferência de dados entre camadas
- **Dependency Injection**: Para injetar dependências e facilitar testes
- **Factory Pattern**: Para criação de objetos complexos
- **Service Layer**: Para encapsular lógica de negócio complexa

## Tecnologias

- **PHP 8.1**: Linguagem de programação
- **Doctrine ORM**: Mapeamento objeto-relacional
- **Doctrine Migrations**: Gerenciamento de migrações de banco de dados
- **MySQL**: Banco de dados relacional
- **Docker**: Containerização da aplicação
- **Nginx**: Servidor web

## Segurança

- **Autenticação**: Sistema baseado em sessões com níveis de acesso
- **Autorização**: Verificação de permissões por rota e recurso
- **Validação de Dados**: Validação rigorosa de todas as entradas
- **Proteção contra Ataques**: Medidas contra CSRF, XSS, SQL Injection
- **Senhas**: Armazenadas com hash seguro (bcrypt)

## Convenções de Código

- **PSR-4**: Para autoloading de classes
- **PSR-12**: Para estilo de código
- **Namespaces**: Seguindo a estrutura de diretórios
- **Tipagem Forte**: Uso de tipos de retorno e parâmetros
- **Imutabilidade**: Preferência por objetos imutáveis quando apropriado