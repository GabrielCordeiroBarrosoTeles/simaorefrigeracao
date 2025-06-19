<?php

namespace BusinessLogic\Services;

use BusinessLogic\Entities\Cliente;
use DataAccess\Repositories\ClienteRepository;

class ClienteService
{
    public function __construct(private ClienteRepository $repository) {}
    
    public function getCliente(int $id): ?Cliente
    {
        $data = $this->repository->findById($id);
        return $data ? Cliente::fromArray($data) : null;
    }
    
    public function getAllClientes(): array
    {
        $data = $this->repository->findAll();
        return array_map(fn($item) => Cliente::fromArray($item), $data);
    }
    
    public function createCliente(array $data): array
    {
        $cliente = Cliente::fromArray($data);
        $errors = $cliente->validate();
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $id = $this->repository->create($cliente->toArray());
        return ['success' => true, 'id' => $id];
    }
    
    public function updateCliente(int $id, array $data): array
    {
        $data['id'] = $id;
        $cliente = Cliente::fromArray($data);
        $errors = $cliente->validate();
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $success = $this->repository->update($id, $cliente->toArray());
        return ['success' => $success];
    }
    
    public function deleteCliente(int $id): bool
    {
        return $this->repository->delete($id);
    }
}