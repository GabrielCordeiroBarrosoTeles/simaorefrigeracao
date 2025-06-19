<?php
// Carregar o bootstrap do sistema
require_once 'bootstrap.php';

// Obter a URL solicitada
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/simaorefrigeracao'; // Ajuste conforme necessário

// Remover o caminho base e parâmetros de consulta
$request_uri = str_replace($base_path, '', $request_uri);
$request_uri = strtok($request_uri, '?');

// Rotas
$routes = [
    // Rotas públicas
    '/' => ['HomeController', 'index'],
    '/contato' => ['ContatoController', 'index'],
    '/processar-contato' => ['ContatoController', 'processar'],
    
    // Rotas de autenticação
    '/admin/login' => ['AuthController', 'loginForm'],
    '/admin/autenticar' => ['AuthController', 'autenticar'],
    '/admin/logout' => ['AuthController', 'logout'],
    
    // Rotas administrativas
    '/admin' => ['Admin\DashboardController', 'index'],
    '/admin/dashboard' => ['Admin\DashboardController', 'index'],
    
    // Rotas de serviços
    '/admin/servicos' => ['Admin\ServicosController', 'index'],
    '/admin/servicos/novo' => ['Admin\ServicosController', 'create'],
    '/admin/servicos/salvar' => ['Admin\ServicosController', 'store'],
    '/admin/servicos/editar' => ['Admin\ServicosController', 'edit'],
    '/admin/servicos/atualizar' => ['Admin\ServicosController', 'update'],
    '/admin/servicos/excluir' => ['Admin\ServicosController', 'delete'],
    
    // Rotas de clientes
    '/admin/clientes' => ['Admin\ClientesController', 'index'],
    '/admin/clientes/novo' => ['Admin\ClientesController', 'create'],
    '/admin/clientes/salvar' => ['Admin\ClientesController', 'store'],
    '/admin/clientes/editar' => ['Admin\ClientesController', 'edit'],
    '/admin/clientes/atualizar' => ['Admin\ClientesController', 'update'],
    '/admin/clientes/excluir' => ['Admin\ClientesController', 'delete'],
    '/admin/clientes/agendamentos' => ['Admin\ClientesController', 'agendamentos'],
    
    // Rotas de técnicos
    '/admin/tecnicos' => ['Admin\TecnicosController', 'index'],
    '/admin/tecnicos/novo' => ['Admin\TecnicosController', 'create'],
    '/admin/tecnicos/salvar' => ['Admin\TecnicosController', 'store'],
    '/admin/tecnicos/editar' => ['Admin\TecnicosController', 'edit'],
    '/admin/tecnicos/atualizar' => ['Admin\TecnicosController', 'update'],
    '/admin/tecnicos/excluir' => ['Admin\TecnicosController', 'delete'],
    '/admin/tecnicos/agendamentos' => ['Admin\TecnicosController', 'agendamentos'],
    '/admin/tecnicos/api' => ['Admin\TecnicosController', 'api'],
    
    // Rotas de agendamentos
    '/admin/agendamentos' => ['Admin\AgendamentosController', 'index'],
    '/admin/agendamentos/calendario' => ['Admin\AgendamentosController', 'calendario'],
    '/admin/agendamentos/novo' => ['Admin\AgendamentosController', 'create'],
    '/admin/agendamentos/salvar' => ['Admin\AgendamentosController', 'store'],
    '/admin/agendamentos/editar' => ['Admin\AgendamentosController', 'edit'],
    '/admin/agendamentos/atualizar' => ['Admin\AgendamentosController', 'update'],
    '/admin/agendamentos/excluir' => ['Admin\AgendamentosController', 'delete'],
    '/admin/agendamentos/api' => ['Admin\AgendamentosController', 'api'],
    
    // Rotas para técnicos logados
    '/tecnico' => ['TecnicoController', 'index'],
    '/tecnico/calendario' => ['TecnicoController', 'calendario'],
    '/tecnico/agendamento' => ['TecnicoController', 'agendamento'],
    '/tecnico/atualizar-status' => ['TecnicoController', 'atualizarStatus'],
    '/tecnico/api' => ['TecnicoController', 'api'],
    
    // Rota de erro
    '/erro' => ['ErrorController', 'index'],
];

// Verificar se a rota existe
if (isset($routes[$request_uri])) {
    $controller_name = $routes[$request_uri][0];
    $action_name = $routes[$request_uri][1];
    
    // Carregar o controlador
    if (strpos($controller_name, 'Admin\\') === 0) {
        $controller_file = CONTROLLERS_DIR . '/' . str_replace('\\', '/', $controller_name) . '.php';
        $controller_name = str_replace('Admin\\', '', $controller_name);
    } else {
        $controller_file = CONTROLLERS_DIR . '/' . $controller_name . '.php';
    }
    
    if (file_exists($controller_file)) {
        require_once $controller_file;
        
        // Instanciar o controlador e chamar a ação
        $controller = new $controller_name();
        
        // Verificar se o método existe
        if (method_exists($controller, $action_name)) {
            $controller->$action_name();
        } else {
            // Método não encontrado
            error_log("Método $action_name não encontrado no controlador $controller_name");
            http_response_code(404);
            require VIEWS_DIR . '/404.php';
        }
    } else {
        // Controlador não encontrado
        error_log("Controlador $controller_file não encontrado");
        http_response_code(404);
        require VIEWS_DIR . '/404.php';
    }
} else {
    // Rota não encontrada
    error_log("Rota $request_uri não encontrada");
    http_response_code(404);
    require VIEWS_DIR . '/404.php';
}
