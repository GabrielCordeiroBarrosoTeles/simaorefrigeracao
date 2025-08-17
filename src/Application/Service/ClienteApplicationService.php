<?php

namespace App\Application\Service;

use App\Application\DTO\ClienteDTO;
use App\Application\Factory\ClienteFactory;
use App\Domain\Service\ClienteService;
use App\Infrastructure\Event\EventDispatcher;
use App\Application\Event\ClienteCriadoEvent;
use App\Application\Event\ClienteAtualizadoEvent;

class ClienteApplicationService
{
    public function __construct(
        private ClienteService $clienteService,
        private ClienteFactory $clienteFactory,
        private EventDispatcher $eventDispatcher
    ) {}

    public function criarCliente(ClienteDTO $dto): ClienteDTO
    {
        $cliente = $this->clienteFactory->createFromDTO($dto);
        $clienteCriado = $this->clienteService->criarCliente($cliente);
        
        $this->eventDispatcher->dispatch(new ClienteCriadoEvent($clienteCriado));
        
        return $this->clienteFactory->createDTOFromEntity($clienteCriado);
    }

    public function atualizarCliente(int $id, ClienteDTO $dto): ClienteDTO
    {
        $cliente = $this->clienteFactory->createFromDTO($dto);
        $clienteAtualizado = $this->clienteService->atualizarCliente($id, $cliente);
        
        $this->eventDispatcher->dispatch(new ClienteAtualizadoEvent($clienteAtualizado));
        
        return $this->clienteFactory->createDTOFromEntity($clienteAtualizado);
    }

    public function buscarPorId(int $id): ClienteDTO
    {
        $cliente = $this->clienteService->buscarPorId($id);
        return $this->clienteFactory->createDTOFromEntity($cliente);
    }

    public function listarTodos(): array
    {
        $clientes = $this->clienteService->listarTodos();
        return array_map(
            fn($cliente) => $this->clienteFactory->createDTOFromEntity($cliente),
            $clientes
        );
    }

    public function buscarPorFiltros(array $filtros): array
    {
        $clientes = $this->clienteService->buscarPorFiltros($filtros);
        return array_map(
            fn($cliente) => $this->clienteFactory->createDTOFromEntity($cliente),
            $clientes
        );
    }

    public function excluirCliente(int $id): void
    {
        $this->clienteService->excluirCliente($id);
    }
}