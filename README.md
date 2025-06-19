# Sistema Simão Refrigeração

Sistema de gestão para empresa de refrigeração com arquitetura em camadas independentes.

## Arquitetura

```mermaid
graph TD
    SQL[MySQL Database]
    NoSQL[NoSQL Database]
    Storage[File Storage]
    
    AD[Camada de Acesso aos Dados]
    CL[Camada de Lógica de Negócio]
    CA[Camada de Apresentação]
    
    CLI[Interface CLI]
    API[API REST]
    WEB[Interface Web]
    
    SQL --> AD
    NoSQL --> AD
    Storage --> AD
    
    AD --> CL
    CL --> CA
    
    CA --> CLI
    CA --> API
    CA --> WEB
    
    %% Estilos
    style SQL fill:#f9f9f9,stroke:#d00,stroke-width:2px
    style NoSQL fill:#f9f9f9,stroke:#d00,stroke-width:2px
    style Storage fill:#f9f9f9,stroke:#00f,stroke-width:2px
    style AD fill:#8e44ad,color:#fff,font-weight:bold
    style CL fill:#c0392b,color:#fff,font-weight:bold
    style CA fill:#2980b9,color:#fff,font-weight:bold
    style CLI fill:#ecf0f1,stroke:#2980b9,stroke-width:2px
    style API fill:#ecf0f1,stroke:#c0392b,stroke-width:2px
    style WEB fill:#ecf0f1,stroke:#2980b9,stroke-width:2px
```

## Nova Estrutura do Projeto

```
simaorefrigeracao/
├── app/                      # Aplicação Next.js/React
│   ├── admin/                # Páginas de administração React
│   ├── globals.css           # Estilos globais
│   ├── layout.tsx            # Layout principal
│   └── page.tsx              # Página inicial
├── bin/                      # Binários e executáveis
├── components/               # Componentes React reutilizáveis
│   └── ui/                   # Componentes de UI
├── config/                   # Configurações do sistema
│   ├── config.php            # Configurações gerais
│   └── database.php          # Configuração do banco de dados
├── controllers/              # Controladores PHP
│   ├── Admin/                # Controladores administrativos
│   └── ...                   # Outros controladores
├── docker/                   # Configurações Docker
├── helpers/                  # Funções auxiliares
├── hooks/                    # React hooks
├── lib/                      # Bibliotecas e utilitários
├── public/                   # Arquivos públicos acessíveis via web
│   ├── admin/                # Páginas administrativas PHP
│   ├── api/                  # Endpoints da API
│   ├── assets/               # Recursos estáticos (CSS, JS, imagens)
│   ├── tecnico/              # Páginas do técnico
│   └── index.php             # Ponto de entrada principal
├── scripts/                  # Scripts de utilidade e manutenção
├── src/                      # Código fonte principal
│   ├── Application/          # Camada de aplicação
│   ├── Domain/               # Camada de domínio
│   ├── Infrastructure/       # Camada de infraestrutura
│   └── Presentation/         # Camada de apresentação
├── styles/                   # Estilos globais
├── vendor/                   # Dependências Composer (gerado automaticamente)
├── views/                    # Templates e visualizações
│   ├── admin/                # Views administrativas
│   ├── public/               # Views públicas
│   └── tecnico/              # Views do técnico
├── .dockerignore             # Arquivos ignorados pelo Docker
├── .env.example              # Exemplo de variáveis de ambiente
├── .gitignore                # Arquivos ignorados pelo Git
├── .htaccess                 # Configurações do Apache
├── bootstrap.php             # Inicialização do sistema
├── composer.json             # Dependências PHP
├── docker-compose.yml        # Configuração do Docker Compose
├── index.php                 # Ponto de entrada principal
├── next.config.mjs           # Configuração do Next.js
├── package.json              # Dependências JavaScript
├── tailwind.config.ts        # Configuração do Tailwind CSS
└── tsconfig.json             # Configuração do TypeScript
```

## Princípios da Arquitetura

### 1. Independência de Tecnologia
- Cada camada é independente de tecnologia específica
- Interfaces definem contratos entre camadas
- Fácil substituição de implementações

### 2. Separação de Responsabilidades
- **Acesso aos Dados**: Persistência e recuperação
- **Lógica de Negócio**: Regras e validações
- **Apresentação**: Interface com usuário

### 3. Inversão de Dependência
- Camadas superiores não dependem de implementações
- Uso de interfaces e injeção de dependência
- Container de dependências centralizado

## Tecnologias Atuais

- **Backend**: PHP 8+
- **Frontend**: Next.js + React
- **Banco**: MySQL
- **Estilo**: Tailwind CSS

## Como Usar

### Instalação
```bash
composer install
npm install
```

### Configuração
```bash
cp .env.example .env
# Configure as variáveis de ambiente
```

### Execução
```bash
# Desenvolvimento
npm run dev
php -S localhost:8000 -t public
```

### Reorganização de Arquivos
Para organizar os arquivos da raiz para a nova estrutura:
```bash
php reorganize_structure.php
```

## Exemplos de Uso

### API REST
```bash
GET /api/clientes          # Listar clientes
POST /api/clientes         # Criar cliente
PUT /api/clientes/{id}     # Atualizar cliente
DELETE /api/clientes/{id}  # Excluir cliente
```

### Interface Web
```
/admin/dashboard           # Dashboard administrativo
/tecnico/agendamentos      # Agendamentos do técnico
```

## Vantagens da Arquitetura

1. **Flexibilidade**: Troca fácil de tecnologias
2. **Testabilidade**: Cada camada pode ser testada isoladamente
3. **Manutenibilidade**: Código organizado e limpo
4. **Escalabilidade**: Fácil adição de novas funcionalidades
5. **Reutilização**: Lógica de negócio compartilhada entre interfaces