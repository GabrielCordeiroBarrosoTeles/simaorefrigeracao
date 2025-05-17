# Arquitetura do Sistema Simão Refrigeração

## Visão Geral
O sistema Simão Refrigeração é uma aplicação web PHP para gerenciamento de serviços de manutenção de ar-condicionado, com três áreas principais:
- **Área Pública**: Landing page para visitantes
- **Área Administrativa**: Gerenciamento completo do sistema
- **Área do Técnico**: Acesso restrito para técnicos visualizarem e atualizarem seus agendamentos

## Estrutura de Diretórios

```
simaorefrigeracao/
├── app/                    # Núcleo da aplicação
│   ├── config/             # Configurações do sistema
│   ├── controllers/        # Controladores
│   ├── models/             # Modelos de dados
│   ├── helpers/            # Funções auxiliares
│   └── core/               # Classes principais do sistema
├── public/                 # Ponto de entrada da aplicação
│   ├── index.php           # Front controller
│   ├── assets/             # Recursos estáticos
│   │   ├── css/            # Folhas de estilo
│   │   ├── js/             # Scripts JavaScript
│   │   ├── images/         # Imagens
│   │   └── fonts/          # Fontes
│   └── uploads/            # Arquivos enviados pelos usuários
├── views/                  # Templates e views
│   ├── admin/              # Views da área administrativa
│   ├── tecnico/            # Views da área do técnico
│   ├── public/             # Views da área pública
│   └── shared/             # Componentes compartilhados
└── vendor/                 # Dependências externas (opcional)
```

## Padrão MVC
O sistema segue o padrão Model-View-Controller (MVC):
- **Models**: Responsáveis pela lógica de negócios e acesso ao banco de dados
- **Views**: Responsáveis pela apresentação dos dados
- **Controllers**: Responsáveis por receber requisições, processar dados e retornar respostas

## Fluxo de Requisição
1. Todas as requisições são direcionadas para o `public/index.php`
2. O sistema de rotas identifica o controlador e a ação correspondentes
3. O controlador processa a requisição, interage com os modelos e renderiza a view apropriada
4. A resposta é enviada ao cliente

## Autenticação e Autorização
- Sistema de login baseado em sessões PHP
- Níveis de acesso: admin, editor, tecnico, tecnico_adm
- Middleware de autenticação para proteger rotas restritas

## Banco de Dados
- MySQL/MariaDB com tabelas relacionais
- Conexão via PDO para segurança e flexibilidade
- Principais entidades: Usuários, Clientes, Técnicos, Serviços, Agendamentos

## Convenções de Código
- PSR-4 para autoloading de classes
- PSR-12 para estilo de código
- Nomes de classes em PascalCase
- Nomes de métodos e variáveis em camelCase
- Constantes em UPPER_SNAKE_CASE
- Indentação com 4 espaços

## Segurança
- Proteção contra SQL Injection via prepared statements
- Proteção contra CSRF com tokens
- Senhas armazenadas com hash seguro (bcrypt)
- Validação de entrada de dados
- Sanitização de saída de dados