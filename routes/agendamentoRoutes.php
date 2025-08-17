<?php

use App\Presentation\Controller\AgendamentoController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('agendamentos_index', new Route('/api/agendamentos', [
    '_controller' => [AgendamentoController::class, 'index']
], [], [], '', [], ['GET']));

$routes->add('agendamentos_show', new Route('/api/agendamentos/{id}', [
    '_controller' => [AgendamentoController::class, 'show']
], ['id' => '\d+'], [], '', [], ['GET']));

$routes->add('agendamentos_store', new Route('/api/agendamentos', [
    '_controller' => [AgendamentoController::class, 'store']
], [], [], '', [], ['POST']));

$routes->add('agendamentos_update', new Route('/api/agendamentos/{id}', [
    '_controller' => [AgendamentoController::class, 'update']
], ['id' => '\d+'], [], '', [], ['PUT']));

$routes->add('agendamentos_destroy', new Route('/api/agendamentos/{id}', [
    '_controller' => [AgendamentoController::class, 'destroy']
], ['id' => '\d+'], [], '', [], ['DELETE']));

$routes->add('agendamentos_by_cliente', new Route('/api/agendamentos/cliente/{clienteId}', [
    '_controller' => [AgendamentoController::class, 'byCliente']
], ['clienteId' => '\d+'], [], '', [], ['GET']));

$routes->add('agendamentos_by_tecnico', new Route('/api/agendamentos/tecnico/{tecnicoId}', [
    '_controller' => [AgendamentoController::class, 'byTecnico']
], ['tecnicoId' => '\d+'], [], '', [], ['GET']));

return $routes;