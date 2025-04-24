# Simão Refrigeração

> Sistema web para gestão de serviços, técnicos, clientes e agendamentos, com site institucional para apresentação da empresa.

## Índice

- [Estrutura do Projeto](#estrutura-do-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Configuração](#configuração)
  - [Pré-requisitos](#pré-requisitos)
  - [Instalação](#instalação)
- [Scripts Disponíveis](#scripts-disponíveis)
- [Contribuição](#contribuição)
- [Licença](#licença)

---

## Estrutura do Projeto

```bash
.
├── app/                       # Frontend em Next.js
│   ├── globals.css            # Estilos globais (Tailwind CSS)
│   ├── layout.tsx             # Layout principal
│   └── page.tsx               # Página inicial
├── assets/                    # Arquivos estáticos (CSS, JS, imagens)
│   ├── css/                   # Estilos personalizados
│   └── js/                    # Scripts JavaScript
├── components/                # Componentes React reutilizáveis
│   ├── theme-provider.tsx
│   └── ui/                    # Biblioteca de UI
├── config/                    # Configurações do sistema
│   ├── config.php             # Configurações gerais
│   └── database.php           # Conexão com MySQL
├── controllers/               # Controladores PHP (MVC)
│   ├── Admin/                 # Área administrativa
│   ├── ContatoController.php  # Lógica de envio de contato
│   └── HomeController.php     # Lógica de exibição da home
├── helpers/                   # Funções auxiliares PHP
│   └── functions.php
├── hooks/                     # Hooks React customizados
│   ├── use-mobile.tsx
│   └── use-toast.ts
├── includes/                  # Partes de layout PHP
│   ├── header.php
│   └── footer.php
├── lib/                       # Utilitários TS
│   └── utils.ts
├── public/                    # Arquivos públicos
├── styles/                    # Estilos adicionais
├── views/                     # Views PHP
│   ├── admin/                 # Painel administrativo
│   └── home.php               # Página inicial
├── database.sql               # Script de criação do banco de dados
├── next.config.mjs            # Config do Next.js
├── package.json               # Dependências e scripts
├── postcss.config.mjs         # Config do PostCSS
├── tailwind.config.ts         # Config do Tailwind CSS
└── tsconfig.json              # Config do TypeScript
```

## Funcionalidades

### Site Institucional

- Página inicial personalizada com informações da empresa.
- Seção destacando serviços oferecidos.
- Depoimentos de clientes.
- Formulário de contato com validação e envio.

### Painel Administrativo

- CRUD de serviços, técnicos, clientes e agendamentos.
- Mensagens flash para feedback ao usuário.
- Proteção CSRF em todos os formulários.
- Autenticação de administradores.

### Banco de Dados

- Tabelas: usuários, serviços, clientes, técnicos, agendamentos, depoimentos e configurações.
- Dados iniciais para facilitar testes e desenvolvimento.

## Tecnologias Utilizadas

### Frontend

- **Next.js** (React)
- **Tailwind CSS**
- **TypeScript**

### Backend

- **PHP 7.4+**
- **PDO** (MySQL)

### Banco de Dados

- **MySQL 5.7+**

### Outras Ferramentas

- **PostCSS**
- **Font Awesome**
- **Lucide Icons** (React)

## Configuração

### Pré-requisitos

- **PHP** 7.4 ou superior
- **MySQL** 5.7 ou superior
- **Node.js** 16 ou superior
- **Composer** (gerenciador de pacotes PHP)

### Instalação

1. Clone este repositório:
   ```bash
   git clone https://github.com/GabrielCordeiroBarrosoTeles/simaorefrigeracao.git
   cd simaorefrigeracao
   ```

2. Configure o banco de dados:
   - Crie um banco MySQL.
   - Importe `database.sql`:
     ```bash
     mysql -u seu_usuario -p nome_do_banco < database.sql
     ```
   - Atualize as credenciais em `config/database.php`.

3. Instale as dependências do frontend:
   ```bash
   cd app
   npm install
   ```

4. Inicie o servidor de desenvolvimento do Next.js:
   ```bash
   npm run dev
   ```

5. Configure e execute o servidor PHP (por exemplo, com o built-in ou Apache/Nginx) apontando para o diretório principal.

## Scripts Disponíveis

No diretório `app`:

- `npm run dev` — Inicia o servidor de desenvolvimento.
- `npm run build` — Compila para produção.
- `npm run start` — Inicia em modo produção.

## Contribuição

Contribuições são bem-vindas! Siga estes passos:

1. Fork do repositório.
2. Crie uma branch (`git checkout -b feature/minha-funcionalidade`).
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`).
4. Push para a branch (`git push origin feature/minha-funcionalidade`).
5. Abra um Pull Request.

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

