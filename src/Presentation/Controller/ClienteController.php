<?php

namespace App\Presentation\Controller;

use App\Application\DTO\ClienteDTO;
use App\Application\Service\ClienteApplicationService;
use App\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ClienteController
{
    public function __construct(
        private ClienteApplicationService $clienteService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $clientes = $this->clienteService->listarTodos();
            return new JsonResponse([
                'success' => true,
                'data' => array_map(fn($cliente) => $cliente->toArray(), $clientes)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao listar clientes'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->buscarPorId($id);
            return new JsonResponse([
                'success' => true,
                'data' => $cliente->toArray()
            ]);
        } catch (DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $dto = ClienteDTO::fromArray($data);
            
            $cliente = $this->clienteService->criarCliente($dto);
            
            return new JsonResponse([
                'success' => true,
                'data' => $cliente->toArray(),
                'message' => 'Cliente criado com sucesso'
            ], Response::HTTP_CREATED);
        } catch (DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $dto = ClienteDTO::fromArray($data);
            
            $cliente = $this->clienteService->atualizarCliente($id, $dto);
            
            return new JsonResponse([
                'success' => true,
                'data' => $cliente->toArray(),
                'message' => 'Cliente atualizado com sucesso'
            ]);
        } catch (DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->clienteService->excluirCliente($id);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Cliente excluído com sucesso'
            ]);
        } catch (DomainException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $filtros = $request->query->all();
            $clientes = $this->clienteService->buscarPorFiltros($filtros);
            
            return new JsonResponse([
                'success' => true,
                'data' => array_map(fn($cliente) => $cliente->toArray(), $clientes)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar clientes'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}