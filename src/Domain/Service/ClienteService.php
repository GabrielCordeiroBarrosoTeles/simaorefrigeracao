<?php

namespace App\Domain\Service;

use App\Domain\Entity\Cliente;
use App\Domain\Repository\ClienteRepositoryInterface;
use App\Domain\Exception\ClienteJaExisteException;
use App\Domain\Exception\ClienteNaoEncontradoException;

class ClienteService
{
    public function __construct(
        private ClienteRepositoryInterface $clienteRepository,
        private ValidationService $validationService
    ) {}

    public function criarCliente(Cliente $cliente): Cliente
    {
        $this->validationService->validate($cliente);
        
        $clienteExistente = $this->clienteRepository->findByEmail($cliente->getEmail()->getValue());
        if ($clienteExistente) {
            throw new ClienteJaExisteException('Cliente com este email já existe');
        }

        $this->clienteRepository->save($cliente);
        return $cliente;
    }

    public function atualizarCliente(int $id, Cliente $clienteAtualizado): Cliente
    {
        $cliente = $this->clienteRepository->findById($id);
        if (!$cliente) {
            throw new ClienteNaoEncontradoException('Cliente não encontrado');
        }

        $this->validationService->validate($clienteAtualizado);

        // Verificar se email não está sendo usado por outro cliente
        $clienteComEmail = $this->clienteRepository->findByEmail($clienteAtualizado->getEmail()->getValue());
        if ($clienteComEmail && $clienteComEmail->getId() !== $id) {
            throw new ClienteJaExisteException('Email já está sendo usado por outro cliente');
        }

        $cliente->setNome($clienteAtualizado->getNome())
                ->setEmail($clienteAtualizado->getEmail())
                ->setTelefone($clienteAtualizado->getTelefone())
                ->setEndereco($clienteAtualizado->getEndereco())
                ->setCidade($clienteAtualizado->getCidade())
                ->setEstado($clienteAtualizado->getEstado())
                ->setCep($clienteAtualizado->getCep())
                ->setTipo($clienteAtualizado->getTipo())
                ->setObservacoes($clienteAtualizado->getObservacoes());

        $this->clienteRepository->save($cliente);
        return $cliente;
    }

    public function buscarPorId(int $id): Cliente
    {
        $cliente = $this->clienteRepository->findById($id);
        if (!$cliente) {
            throw new ClienteNaoEncontradoException('Cliente não encontrado');
        }
        return $cliente;
    }

    public function listarTodos(): array
    {
        return $this->clienteRepository->findAll();
    }

    public function buscarPorFiltros(array $filtros): array
    {
        return $this->clienteRepository->findByFilters($filtros);
    }

    public function excluirCliente(int $id): void
    {
        $cliente = $this->buscarPorId($id);
        
        if (!$cliente->getAgendamentos()->isEmpty()) {
            throw new \DomainException('Não é possível excluir cliente com agendamentos');
        }

        $this->clienteRepository->delete($cliente);
    }
}