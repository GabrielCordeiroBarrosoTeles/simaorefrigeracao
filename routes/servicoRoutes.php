<?php

use App\Presentation\Controller\ServicoController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('servicos_index', new Route('/api/servicos', [
    '_controller' => [ServicoController::class, 'index']
], [], [], '', [], ['GET']));

$routes->add('servicos_show', new Route('/api/servicos/{id}', [
    '_controller' => [ServicoController::class, 'show']
], ['id' => '\d+'], [], '', [], ['GET']));

$routes->add('servicos_store', new Route('/api/servicos', [
    '_controller' => [ServicoController::class, 'store']
], [], [], '', [], ['POST']));

$routes->add('servicos_update', new Route('/api/servicos/{id}', [
    '_controller' => [ServicoController::class, 'update']
], ['id' => '\d+'], [], '', [], ['PUT']));

$routes->add('servicos_destroy', new Route('/api/servicos/{id}', [
    '_controller' => [ServicoController::class, 'destroy']
], ['id' => '\d+'], [], '', [], ['DELETE']));

return $routes;