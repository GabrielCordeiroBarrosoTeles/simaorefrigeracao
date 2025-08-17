<?php

use App\Presentation\Controller\AuthController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('auth_login', new Route('/api/auth/login', [
    '_controller' => [AuthController::class, 'login']
], [], [], '', [], ['POST']));

$routes->add('auth_logout', new Route('/api/auth/logout', [
    '_controller' => [AuthController::class, 'logout']
], [], [], '', [], ['POST']));

$routes->add('auth_me', new Route('/api/auth/me', [
    '_controller' => [AuthController::class, 'me']
], [], [], '', [], ['GET']));

$routes->add('auth_refresh', new Route('/api/auth/refresh', [
    '_controller' => [AuthController::class, 'refresh']
], [], [], '', [], ['POST']));

return $routes;