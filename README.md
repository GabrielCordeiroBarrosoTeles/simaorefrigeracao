# Simão Refrigeração

Este repositório contém o código-fonte do sistema da Simão Refrigeração, uma aplicação web para gerenciamento de serviços, técnicos, clientes e agendamentos, além de um site institucional para exibição de informações sobre a empresa.

## Estrutura do Projeto

A estrutura do projeto é organizada da seguinte forma:
. ├── app/ # Diretório para o frontend em Next.js │ ├── globals.css # Estilos globais do Tailwind CSS │ ├── layout.tsx # Layout principal da aplicação │ └── page.tsx # Página inicial ├── assets/ # Arquivos estáticos (CSS, JS, imagens) │ ├── css/ # Estilos personalizados │ ├── js/ # Scripts JavaScript ├── components/ # Componentes React reutilizáveis │ ├── theme-provider.tsx │ └── ui/ # Componentes de interface do usuário ├── config/ # Configurações do sistema │ ├── config.php # Configurações gerais │ └── database.php # Configuração do banco de dados ├── controllers/ # Controladores PHP para lógica de negócios │ ├── Admin/ # Controladores administrativos │ ├── ContatoController.php │ └── HomeController.php ├── helpers/ # Funções auxiliares │ └── functions.php ├── hooks/ # Hooks React personalizados │ ├── use-mobile.tsx │ └── use-toast.ts ├── includes/ # Partes reutilizáveis de layout PHP │ ├── footer.php │ └── header.php ├── lib/ # Utilitários e funções auxiliares │ └── utils.ts ├── public/ # Arquivos públicos acessíveis diretamente ├── styles/ # Estilos adicionais ├── views/ # Arquivos de visualização PHP │ ├── admin/ # Painel administrativo │ └── home.php # Página inicial ├── database.sql # Script SQL para criação do banco de dados ├── next.config.mjs # Configuração do Next.js ├── package.json # Dependências e scripts do projeto ├── postcss.config.mjs # Configuração do PostCSS ├── tailwind.config.ts # Configuração do Tailwind CSS └── tsconfig.json # Configuração do TypeScript


## Funcionalidades

### Site Institucional
- Página inicial com informações sobre a empresa.
- Seção de serviços oferecidos.
- Depoimentos de clientes.
- Formulário de contato com validação e envio de mensagens.

### Painel Administrativo
- Gerenciamento de serviços, técnicos, clientes e agendamentos.
- Exibição de mensagens flash para feedback ao usuário.
- Proteção CSRF em formulários.
- Sistema de autenticação para administradores.

### Banco de Dados
- Estrutura SQL para tabelas de usuários, serviços, clientes, técnicos, agendamentos, depoimentos e configurações.
- Dados iniciais para facilitar a configuração do sistema.

## Tecnologias Utilizadas

### Frontend
- **Next.js**: Framework React para renderização do frontend.
- **Tailwind CSS**: Framework CSS para estilização.
- **TypeScript**: Tipagem estática para JavaScript.

### Backend
- **PHP**: Linguagem de programação para lógica de negócios.
- **PDO**: Interface para acesso ao banco de dados MySQL.

### Banco de Dados
- **MySQL**: Banco de dados relacional para armazenamento de informações.

### Outras Ferramentas
- **PostCSS**: Processador CSS.
- **Font Awesome**: Biblioteca de ícones.
- **Lucide Icons**: Biblioteca de ícones para React.

## Configuração

### Requisitos
- PHP 7.4 ou superior.
- MySQL 5.7 ou superior.
- Node.js 16 ou superior.
- Composer (para dependências PHP).

### Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/simao-refrigeracao.git
   cd simao-refrigeracao

   Configure o banco de dados:

Crie um banco de dados MySQL.
Importe o arquivo database.sql para criar as tabelas e dados iniciais.
Configure o arquivo config/database.php com as credenciais do banco de dados.

Instale as dependências do frontend:

Inicie o servidor de desenvolvimento:

Configure o servidor PHP para servir os arquivos PHP.

Scripts Disponíveis
npm run dev: Inicia o servidor de desenvolvimento do Next.js.
npm run build: Compila o projeto para produção.
npm run start: Inicia o servidor de produção.
Contribuição
Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou enviar pull requests.

Licença
Este projeto está licenciado sob a MIT License.

