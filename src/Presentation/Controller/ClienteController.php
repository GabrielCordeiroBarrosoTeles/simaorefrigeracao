<?php
namespace App\Controller;

use App\Service\ClienteService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ClienteRepository;

class ClienteController
{
    private ClienteService $clienteService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $clienteRepository = new ClienteRepository($entityManager);
        $this->clienteService = new ClienteService($clienteRepository);
    }

    public function index(): array
    {
        return $this->clienteService->listarTodos();
    }

    public function show(int $id): ?array
    {
        $cliente = $this->clienteService->buscarPorId($id);
        
        if (!$cliente) {
            return null;
        }
        
        return [
            'id' => $cliente->getId(),
            'nome' => $cliente->getNome(),
            'email' => $cliente->getEmail(),
            'telefone' => $cliente->getTelefone(),
            'endereco' => $cliente->getEndereco(),
            'cidade' => $cliente->getCidade(),
            'estado' => $cliente->getEstado(),
            'cep' => $cliente->getCep(),
            'tipo' => $cliente->getTipo(),
            'observacoes' => $cliente->getObservacoes(),
            'data_criacao' => $cliente->getDataCriacao()->format('Y-m-d H:i:s'),
            'data_atualizacao' => $cliente->getDataAtualizacao() ? $cliente->getDataAtualizacao()->format('Y-m-d H:i:s') : null
        ];
    }

    public function create(array $dados): ?array
    {
        $cliente = $this->clienteService->criar($dados);
        
        if (!$cliente) {
            return null;
        }
        
        return [
            'id' => $cliente->getId(),
            'nome' => $cliente->getNome(),
            'email' => $cliente->getEmail(),
            'mensagem' => 'Cliente criado com sucesso!'
        ];
    }

    public function update(int $id, array $dados): ?array
    {
        $cliente = $this->clienteService->buscarPorId($id);
        
        if (!$cliente) {
            return null;
        }
        
        $cliente = $this->clienteService->atualizar($cliente, $dados);
        
        return [
            'id' => $cliente->getId(),
            'nome' => $cliente->getNome(),
            'email' => $cliente->getEmail(),
            'mensagem' => 'Cliente atualizado com sucesso!'
        ];
    }

    public function delete(int $id): bool
    {
        $cliente = $this->clienteService->buscarPorId($id);
        
        if (!$cliente) {
            return false;
        }
        
        $this->clienteService->excluir($cliente);
        return true;
    }

    public function findByTipo(string $tipo): array
    {
        return $this->clienteService->buscarPorTipo($tipo);
    }

    public function findByNome(string $nome): array
    {
        return $this->clienteService->buscarPorNome($nome);
    }

    public function countByTipo(string $tipo): int
    {
        return $this->clienteService->contarPorTipo($tipo);
    }
}