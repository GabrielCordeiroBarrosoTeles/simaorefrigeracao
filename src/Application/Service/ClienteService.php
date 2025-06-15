<?php
namespace App\Service;

use App\Entity\Cliente;
use App\Repository\ClienteRepository;

class ClienteService
{
    private ClienteRepository $clienteRepository;

    public function __construct(ClienteRepository $clienteRepository)
    {
        $this->clienteRepository = $clienteRepository;
    }

    public function listarTodos(): array
    {
        return $this->clienteRepository->findAll();
    }

    public function buscarPorId(int $id): ?Cliente
    {
        return $this->clienteRepository->findById($id);
    }

    public function buscarPorEmail(string $email): ?Cliente
    {
        return $this->clienteRepository->findByEmail($email);
    }

    public function buscarPorTipo(string $tipo): array
    {
        return $this->clienteRepository->findByTipo($tipo);
    }

    public function buscarPorNome(string $nome): array
    {
        return $this->clienteRepository->findByNome($nome);
    }

    public function criar(array $dados): Cliente
    {
        $cliente = new Cliente();
        $this->preencherDados($cliente, $dados);
        
        $this->clienteRepository->save($cliente);
        return $cliente;
    }

    public function atualizar(Cliente $cliente, array $dados): Cliente
    {
        $this->preencherDados($cliente, $dados);
        
        $this->clienteRepository->save($cliente);
        return $cliente;
    }

    public function excluir(Cliente $cliente): void
    {
        $this->clienteRepository->remove($cliente);
    }

    public function contarPorTipo(string $tipo): int
    {
        return $this->clienteRepository->countByTipo($tipo);
    }

    private function preencherDados(Cliente $cliente, array $dados): void
    {
        if (isset($dados['nome'])) {
            $cliente->setNome($dados['nome']);
        }
        
        if (isset($dados['email'])) {
            $cliente->setEmail($dados['email']);
        }
        
        if (isset($dados['telefone'])) {
            $cliente->setTelefone($dados['telefone']);
        }
        
        if (isset($dados['endereco'])) {
            $cliente->setEndereco($dados['endereco']);
        }
        
        if (isset($dados['cidade'])) {
            $cliente->setCidade($dados['cidade']);
        }
        
        if (isset($dados['estado'])) {
            $cliente->setEstado($dados['estado']);
        }
        
        if (isset($dados['cep'])) {
            $cliente->setCep($dados['cep']);
        }
        
        if (isset($dados['tipo'])) {
            $cliente->setTipo($dados['tipo']);
        }
        
        if (isset($dados['observacoes'])) {
            $cliente->setObservacoes($dados['observacoes']);
        }
    }
}