<?php

namespace Presentation\API;

use BusinessLogic\Services\ClienteService;

class ClienteController
{
    public function __construct(private ClienteService $service) {}
    
    public function index(): void
    {
        $clientes = $this->service->getAllClientes();
        $this->jsonResponse(array_map(fn($c) => $c->toArray(), $clientes));
    }
    
    public function show(int $id): void
    {
        $cliente = $this->service->getCliente($id);
        
        if (!$cliente) {
            $this->jsonResponse(['error' => 'Cliente não encontrado'], 404);
            return;
        }
        
        $this->jsonResponse($cliente->toArray());
    }
    
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->createCliente($data);
        
        if (!$result['success']) {
            $this->jsonResponse(['errors' => $result['errors']], 400);
            return;
        }
        
        $this->jsonResponse(['id' => $result['id']], 201);
    }
    
    public function update(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->updateCliente($id, $data);
        
        if (!$result['success']) {
            $this->jsonResponse(['errors' => $result['errors']], 400);
            return;
        }
        
        $this->jsonResponse(['success' => true]);
    }
    
    public function destroy(int $id): void
    {
        $success = $this->service->deleteCliente($id);
        $this->jsonResponse(['success' => $success]);
    }
    
    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}