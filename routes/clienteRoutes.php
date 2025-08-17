<?php

use App\Presentation\Controller\ClienteController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('clientes_index', new Route('/api/clientes', [
    '_controller' => [ClienteController::class, 'index']
], [], [], '', [], ['GET']));

$routes->add('clientes_show', new Route('/api/clientes/{id}', [
    '_controller' => [ClienteController::class, 'show']
], ['id' => '\d+'], [], '', [], ['GET']));

$routes->add('clientes_store', new Route('/api/clientes', [
    '_controller' => [ClienteController::class, 'store']
], [], [], '', [], ['POST']));

$routes->add('clientes_update', new Route('/api/clientes/{id}', [
    '_controller' => [ClienteController::class, 'update']
], ['id' => '\d+'], [], '', [], ['PUT']));

$routes->add('clientes_destroy', new Route('/api/clientes/{id}', [
    '_controller' => [ClienteController::class, 'destroy']
], ['id' => '\d+'], [], '', [], ['DELETE']));

$routes->add('clientes_search', new Route('/api/clientes/search', [
    '_controller' => [ClienteController::class, 'search']
], [], [], '', [], ['GET']));

return $routes;