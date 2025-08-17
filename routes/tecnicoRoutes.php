<?php

use App\Presentation\Controller\TecnicoController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('tecnicos_index', new Route('/api/tecnicos', [
    '_controller' => [TecnicoController::class, 'index']
], [], [], '', [], ['GET']));

$routes->add('tecnicos_show', new Route('/api/tecnicos/{id}', [
    '_controller' => [TecnicoController::class, 'show']
], ['id' => '\d+'], [], '', [], ['GET']));

$routes->add('tecnicos_store', new Route('/api/tecnicos', [
    '_controller' => [TecnicoController::class, 'store']
], [], [], '', [], ['POST']));

$routes->add('tecnicos_update', new Route('/api/tecnicos/{id}', [
    '_controller' => [TecnicoController::class, 'update']
], ['id' => '\d+'], [], '', [], ['PUT']));

$routes->add('tecnicos_destroy', new Route('/api/tecnicos/{id}', [
    '_controller' => [TecnicoController::class, 'destroy']
], ['id' => '\d+'], [], '', [], ['DELETE']));

$routes->add('tecnicos_ativos', new Route('/api/tecnicos/ativos', [
    '_controller' => [TecnicoController::class, 'ativos']
], [], [], '', [], ['GET']));

return $routes;