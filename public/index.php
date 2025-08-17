<?php

use App\Presentation\Controller\ClienteController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

require_once __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../src/bootstrap.php';

// Criar coleção de rotas
$routes = new RouteCollection();

// Carregar todas as rotas
$clienteRoutes = require __DIR__ . '/../routes/clienteRoutes.php';
$agendamentoRoutes = require __DIR__ . '/../routes/agendamentoRoutes.php';
$servicoRoutes = require __DIR__ . '/../routes/servicoRoutes.php';
$tecnicoRoutes = require __DIR__ . '/../routes/tecnicoRoutes.php';
$authRoutes = require __DIR__ . '/../routes/authRoutes.php';

$routes->addCollection($clienteRoutes);
$routes->addCollection($agendamentoRoutes);
$routes->addCollection($servicoRoutes);
$routes->addCollection($tecnicoRoutes);
$routes->addCollection($authRoutes);

// Processar request
$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($request->getPathInfo());
    
    $controllerClass = $parameters['_controller'][0];
    $method = $parameters['_controller'][1];
    
    $controller = $container->get($controllerClass);
    
    // Remover parâmetros internos
    unset($parameters['_controller'], $parameters['_route']);
    
    // Chamar método do controller
    $response = call_user_func_array([$controller, $method], array_merge([$request], array_values($parameters)));
    
} catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
    $response = new Response('Página não encontrada', 404);
} catch (\Exception $e) {
    $response = new Response('Erro interno: ' . $e->getMessage(), 500);
}

$response->send();