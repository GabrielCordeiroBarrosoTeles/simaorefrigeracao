# Simão Refrigeração

Sistema de gerenciamento para empresa de refrigeração e ar condicionado.

## Estrutura do Projeto

```
simaorefrigeracao/
├── assets/             # Arquivos estáticos (CSS, JS, imagens)
│   ├── css/            # Arquivos CSS
│   ├── js/             # Arquivos JavaScript
│   └── img/            # Imagens
├── config/             # Arquivos de configuração
│   ├── config.php      # Configurações gerais
│   └── database.php    # Configuração do banco de dados
├── controllers/        # Controladores
│   ├── Admin/          # Controladores da área administrativa
│   └── ...             # Outros controladores
├── helpers/            # Funções auxiliares
│   └── functions.php   # Funções globais
├── models/             # Modelos de dados
├── uploads/            # Arquivos enviados pelos usuários
├── views/              # Arquivos de visualização
│   ├── admin/          # Visualizações da área administrativa
│   ├── includes/       # Arquivos incluídos (header, footer, etc.)
│   └── ...             # Outras visualizações
├── .htaccess           # Configurações do Apache
├── bootstrap.php       # Inicialização do sistema
├── index.php           # Ponto de entrada da aplicação
└── README.md           # Documentação do projeto
```

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado

## Instalação

1. Clone o repositório
2. Importe o arquivo `database_completo.sql` para criar o banco de dados
3. Configure o arquivo `config/database.php` com as credenciais do seu banco de dados
4. Certifique-se de que o Apache está configurado para permitir .htaccess
5. Acesse o sistema pelo navegador

## Usuário Padrão

- Email: admin@friocerto.com.br
- Senha: admin123