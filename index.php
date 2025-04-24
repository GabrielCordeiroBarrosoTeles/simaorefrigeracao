<?php
// Iniciar sessão
session_start();

// Configuração do sistema
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Sistema de roteamento simples
$request = $_SERVER['REQUEST_URI'];
$base_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$route = str_replace($base_path, '', $request);

// Remover parâmetros de consulta
$route = explode('?', $route)[0];

// Roteamento
switch ($route) {
    case '/':
    case '':
        require 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;
        
    case '/servicos':
        require 'controllers/ServicosController.php';
        $controller = new ServicosController();
        $controller->index();
        break;
        
    case '/sobre':
        require 'controllers/SobreController.php';
        $controller = new SobreController();
        $controller->index();
        break;
        
    case '/contato':
        require 'controllers/ContatoController.php';
        $controller = new ContatoController();
        $controller->index();
        break;
        
    case '/enviar-contato':
        require 'controllers/ContatoController.php';
        $controller = new ContatoController();
        $controller->enviar();
        break;
        
    // Rotas do painel administrativo
    case '/admin':
    case '/admin/':
        require 'controllers/Admin/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;
        
    case '/admin/login':
        require 'controllers/Admin/AuthController.php';
        $controller = new AuthController();
        $controller->loginForm();
        break;
        
    case '/admin/autenticar':
        require 'controllers/Admin/AuthController.php';
        $controller = new AuthController();
        $controller->autenticar();
        break;
        
    case '/admin/logout':
        require 'controllers/Admin/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
        
    // Rotas de Serviços
    case '/admin/servicos':
        require 'controllers/Admin/ServicosController.php';
        $controller = new ServicosController();
        $controller->index();
        break;
        
    case '/admin/servicos/novo':
        require 'controllers/Admin/ServicosController.php';
        $controller = new ServicosController();
        $controller->create();
        break;
        
    case '/admin/servicos/salvar':
        require 'controllers/Admin/ServicosController.php';
        $controller = new ServicosController();
        $controller->store();
        break;
        
    case '/admin/servicos/editar':
        require 'controllers/Admin/ServicosController.php';
        $controller = new ServicosController();
        $controller->edit();
        break;
        
    case '/admin/servicos/atualizar':
        require 'controllers/Admin/ServicosController.php';
        $controller = new ServicosController();
        $controller->update();
        break;
        
    case '/admin/servicos/excluir':
        require 'controllers/Admin/ServicosController.php';
        $controller = new ServicosController();
        $controller->delete();
        break;
        
    // Rotas de Clientes
    case '/admin/clientes':
        require 'controllers/Admin/ClientesController.php';
        $controller = new ClientesController();
        $controller->index();
        break;
        
    case '/admin/clientes/novo':
        require 'controllers/Admin/ClientesController.php';
        $controller = new ClientesController();
        $controller->create();
        break;
        
    case '/admin/clientes/salvar':
        require 'controllers/Admin/ClientesController.php';
        $controller = new ClientesController();
        $controller->store();
        break;
        
    case '/admin/clientes/editar':
        require 'controllers/Admin/ClientesController.php';
        $controller = new ClientesController();
        $controller->edit();
        break;
        
    case '/admin/clientes/atualizar':
        require 'controllers/Admin/ClientesController.php';
        $controller = new ClientesController();
        $controller->update();
        break;
        
    case '/admin/clientes/excluir':
        require 'controllers/Admin/ClientesController.php';
        $controller = new ClientesController();
        $controller->delete();
        break;
        
    case '/admin/clientes/agendamentos':
        require 'controllers/Admin/ClientesController.php';
        $controller = new ClientesController();
        $controller->agendamentos();
        break;
        
    // Rotas de Técnicos
    case '/admin/tecnicos':
        require 'controllers/Admin/TecnicosController.php';
        $controller = new TecnicosController();
        $controller->index();
        break;
        
    case '/admin/tecnicos/novo':
        require 'controllers/Admin/TecnicosController.php';
        $controller = new TecnicosController();
        $controller->create();
        break;
        
    case '/admin/tecnicos/salvar':
        require 'controllers/Admin/TecnicosController.php';
        $controller = new TecnicosController();
        $controller->store();
        break;
        
    case '/admin/tecnicos/editar':
        require 'controllers/Admin/TecnicosController.php';
        $controller = new TecnicosController();
        $controller->edit();
        break;
        
    case '/admin/tecnicos/atualizar':
        require 'controllers/Admin/TecnicosController.php';
        $controller = new TecnicosController();
        $controller->update();
        break;
        
    case '/admin/tecnicos/excluir':
        require 'controllers/Admin/TecnicosController.php';
        $controller = new TecnicosController();
        $controller->delete();
        break;
        
    case '/admin/tecnicos/agendamentos':
        require 'controllers/Admin/TecnicosController.php';
        $controller = new TecnicosController();
        $controller->agendamentos();
        break;
        
    // Rotas de Agendamentos
    case '/admin/agendamentos':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->index();
        break;
        
    case '/admin/agendamentos/calendario':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->calendario();
        break;
        
    case '/admin/agendamentos/novo':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->create();
        break;
        
    case '/admin/agendamentos/salvar':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->store();
        break;
        
    case '/admin/agendamentos/editar':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->edit();
        break;
        
    case '/admin/agendamentos/atualizar':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->update();
        break;
        
    case '/admin/agendamentos/excluir':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->delete();
        break;
        
    case '/admin/agendamentos/api':
        require 'controllers/Admin/AgendamentosController.php';
        $controller = new AgendamentosController();
        $controller->api();
        break;
        
    // Rotas de Contatos
    case '/admin/contatos':
        require 'controllers/Admin/ContatosController.php';
        $controller = new ContatosController();
        $controller->index();
        break;
        
    default:
        http_response_code(404);
        require 'views/404.php';
        break;
}
