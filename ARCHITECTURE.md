# Arquitetura do Sistema FrioCerto

## Visão Geral

O sistema FrioCerto é uma aplicação web para gerenciamento de serviços de refrigeração, incluindo agendamentos, clientes, técnicos e serviços. A arquitetura segue o padrão MVC (Model-View-Controller) para melhor organização e manutenção do código.

## Estrutura de Diretórios

\`\`\`
simaorefrigeracao/
├── admin/                  # Arquivos administrativos
│   ├── dashboard.php       # Painel principal
│   ├── login.php           # Login administrativo
│   ├── table.php           # Gerenciamento de tabelas
│   ├── form.php            # Formulários genéricos
│   └── ...                 # Outros arquivos administrativos
│
├── app/                    # Núcleo da aplicação
│   ├── layout.tsx          # Layout principal (Next.js)
│   └── page.tsx            # Página inicial (Next.js)
│
├── assets/                 # Recursos estáticos (legado)
│   ├── css/                # Arquivos CSS
│   ├── js/                 # Arquivos JavaScript
│   └── img/                # Imagens
│
├── config/                 # Configurações
│   ├── config.php          # Configurações gerais
│   └── database.php        # Configuração do banco de dados
│
├── controllers/            # Controladores
│   ├── Admin/              # Controladores administrativos
│   │   ├── DashboardController.php
│   │   ├── ServicosController.php
│   │   └── ...
│   ├── HomeController.php
│   ├── ContatoController.php
│   └── ...
│
├── helpers/                # Funções auxiliares
│   └── functions.php       # Funções globais
│
├── includes/               # Arquivos incluídos
│   ├── header.php
│   └── footer.php
│
├── models/                 # Modelos de dados
│   ├── Cliente.php
│   ├── Servico.php
│   └── ...
│
├── public/                 # Arquivos públicos (nova estrutura)
│   ├── css/                # Estilos
│   ├── js/                 # Scripts
│   └── img/                # Imagens
│
├── tests/                  # Testes
│   ├── test_login.php
│   └── ...
│
├── tools/                  # Ferramentas e utilitários
│   ├── debug.php
│   ├── setup_database.php
│   └── ...
│
├── uploads/                # Arquivos enviados
│
├── views/                  # Visualizações
│   ├── admin/              # Visualizações administrativas
│   │   ├── dashboard.php
│   │   ├── includes/       # Componentes reutilizáveis
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   └── sidebar.php
│   │   └── ...
│   ├── home.php
│   ├── contato.php
│   └── ...
│
├── .htaccess               # Configurações do Apache
├── bootstrap.php           # Inicialização do sistema
├── index.php               # Ponto de entrada principal
└── README.md               # Documentação
\`\`\`

## Padrões de Código

1. **Nomenclatura**:
   - Classes: PascalCase (ex: ClienteController)
   - Métodos e funções: camelCase (ex: getClientes)
   - Variáveis: snake_case (ex: $nome_cliente)
   - Constantes: UPPER_CASE (ex: MAX_FILE_SIZE)

2. **Organização de Arquivos**:
   - Um arquivo por classe
   - Nome do arquivo igual ao nome da classe
   - Arquivos de visualização com nomes descritivos

3. **Segurança**:
   - Validação de entrada de dados
   - Prevenção de SQL Injection usando prepared statements
   - Autenticação e autorização adequadas

## Fluxo de Dados

1. O usuário acessa uma URL
2. O index.php roteia a solicitação para o controlador apropriado
3. O controlador processa a solicitação, interage com os modelos
4. Os modelos acessam o banco de dados
5. O controlador passa os dados para a visualização
6. A visualização renderiza o HTML final
7. A resposta é enviada ao usuário

## Banco de Dados

O sistema utiliza MySQL/MariaDB com as seguintes tabelas principais:

- clientes
- tecnicos
- servicos
- agendamentos
- usuarios
- contatos
- depoimentos
- configuracoes

## Autenticação

O sistema utiliza autenticação baseada em sessão com os seguintes recursos:

- Login com nome de usuário e senha
- Níveis de acesso (admin, técnico)
- Proteção contra força bruta
- Tempo limite de sessão

## Manutenção e Desenvolvimento

Para adicionar novos recursos:

1. Crie/modifique os modelos necessários
2. Implemente a lógica nos controladores
3. Crie/atualize as visualizações
4. Atualize as rotas no index.php
5. Teste exaustivamente

## Considerações de Segurança

- Mantenha as bibliotecas atualizadas
- Use HTTPS em produção
- Implemente validação de entrada em todos os formulários
- Siga as melhores práticas de segurança web
