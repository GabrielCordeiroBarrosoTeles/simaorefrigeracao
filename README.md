# Simão Refrigeração

Sistema de gerenciamento para empresa de refrigeração e ar condicionado.

---

## Visão Geral

O **Simão Refrigeração** é uma aplicação web em PHP para gerenciamento de serviços de manutenção de ar-condicionado e refrigeração, composta por três áreas principais:

* **Área Pública**: Landing page para visitantes apresentarem-se à empresa.
* **Área Administrativa**: Painel completo para gestão de clientes, técnicos, agendamentos e relatórios.
* **Área do Técnico**: Interface restrita para técnicos visualizarem, atualizarem e finalizarem seus agendamentos.

---

## Estrutura do Projeto

```
simaorefrigeracao/
├── app/                    # Núcleo da aplicação
│   ├── config/             # Configurações do sistema
│   │   ├── config.php      # Configurações gerais
│   │   └── database.php    # Configuração do banco de dados
│   ├── controllers/        # Controladores (MVC)
│   │   ├── Admin/          # Controladores da área administrativa
│   │   └── Tecnico/        # Controladores da área do técnico
│   ├── models/             # Modelos de dados (entidades)
│   ├── helpers/            # Funções auxiliares
│   │   └── functions.php   # Funções globais
│   └── core/               # Classes e base do framework interno
├── public/                 # Ponto de entrada e recursos públicos
│   ├── index.php           # Front controller
│   ├── .htaccess           # Regras de URL e segurança
│   ├── assets/             # Recursos estáticos
│   │   ├── css/            # Folhas de estilo
│   │   ├── js/             # Scripts JavaScript
│   │   ├── images/         # Imagens
│   │   └── fonts/          # Fontes
│   └── uploads/            # Arquivos enviados pelos usuários
├── views/                  # Templates e visualizações
│   ├── admin/              # Views da área administrativa
│   ├── tecnico/            # Views da área do técnico
│   ├── public/             # Views da área pública
│   └── shared/             # Componentes compartilhados (header, footer, etc.)
├── vendor/                 # Dependências externas (Composer)
├── assets/                 # (Opcional) recursos legados
│   ├── css/
│   ├── js/
│   └── img/
├── uploads/                # (Opcional) uploads legados
├── bootstrap.php           # Inicialização do sistema
├── composer.json           # Dependências e autoload (PSR-4)
├── .env                    # Variáveis de ambiente (não versionado)
├── .env.example            # Exemplo de variáveis de ambiente
├── .gitignore              # Arquivos e pastas ignorados pelo Git
└── README.md               # Documentação do projeto
```

---

## Padrão MVC

O sistema segue o padrão **Model-View-Controller (MVC)**:

* **Models**: Lógica de negócio e acesso ao banco de dados.
* **Views**: Apresentação dos dados ao usuário.
* **Controllers**: Recebem requisições, interagem com Models e retornam Views.

All requests passam pelo front controller (`public/index.php`) e são roteados conforme configuração em `app/config`.

---

## Fluxo de Requisição

1. O usuário faz uma requisição à aplicação.
2. `public/index.php` inicializa o sistema e carrega dependências.
3. O sistema de rotas determina qual Controller e ação chamar.
4. O Controller processa dados (chama Models, valida inputs, etc.) e escolhe uma View.
5. A View é renderizada e a resposta é enviada ao cliente.

---

## Requisitos

* PHP 7.4 ou superior
* MySQL 5.7 ou superior (ou MariaDB)
* Apache com `mod_rewrite` habilitado
* Composer (para autoload e dependências)

---

## Instalação

1. Clone este repositório:

   ```bash
   git clone https://github.com/GabrielCordeiroBarrosoTeles/simaorefrigeracao.git
   ```
2. Instale dependências (se houver `composer.json`):

   ```bash
   cd simaorefrigeracao
   composer install
   ```
3. Copie e configure o `.env`:

   ```bash
   cp .env.example .env
   # Ajuste as variáveis de conexão ao banco
   ```
4. Importe o esquema do banco de dados:

   ```bash
   mysql -u usuario -p nome_do_banco < database_completo.sql
   ```
5. Configure o Apache (document root em `public/`) e permita `.htaccess`.
6. Acesse o sistema via navegador.

---

## Usuário Padrão (Admin)

* Email: `admin@friocerto.com.br`
* Senha: `admin123`

---

## Autenticação e Autorização

* Gerenciada via *sessions* PHP.
* Níveis de acesso: `admin`, `tecnico`, `public`.
* Middleware de verificação para proteger rotas restritas.

---

## Banco de Dados

* Conexão via PDO com *prepared statements*.
* Principais tabelas:

  * `usuarios` (admin e técnicos)
  * `clientes`
  * `servicos`
  * `agendamentos`
  * `pagamentos`

---

## Segurança

* Proteção contra SQL Injection via prepared statements.
* Tokens CSRF em formulários.
* Sanitização de saídas (XSS).
* Hash de senhas com `password_hash()` (bcrypt).

---

## Convenções de Código

* **Autoload**: PSR-4 (Composer)
* **Estilo**: PSR-12
* **Classes**: `PascalCase`
* **Métodos/Variáveis**: `camelCase`
* **Constantes**: `UPPER_SNAKE_CASE`
* **Indentação**: 4 espaços

---

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).
