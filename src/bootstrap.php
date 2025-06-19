<?php

// Autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Configurações
$config = [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'simaorefrigeracao',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? ''
    ]
];

// Container de dependências
class Container
{
    private array $services = [];
    
    public function set(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
    }
    
    public function get(string $name): mixed
    {
        if (!isset($this->services[$name])) {
            throw new Exception("Service {$name} not found");
        }
        
        return $this->services[$name]();
    }
}

$container = new Container();

// Registrar serviços
$container->set('database', fn() => new \DataAccess\Database\MySQLDatabase(
    $config['database']['host'],
    $config['database']['name'],
    $config['database']['user'],
    $config['database']['pass']
));

$container->set('clienteRepository', fn() => new \DataAccess\Repositories\ClienteRepository(
    $container->get('database')
));

$container->set('clienteService', fn() => new \BusinessLogic\Services\ClienteService(
    $container->get('clienteRepository')
));

$container->set('clienteApiController', fn() => new \Presentation\API\ClienteController(
    $container->get('clienteService')
));

$container->set('clienteWebController', fn() => new \Presentation\Web\ClienteWebController(
    $container->get('clienteService')
));

return $container;