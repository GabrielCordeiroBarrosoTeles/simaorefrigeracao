<?php

namespace Presentation\Web;

use BusinessLogic\Services\ClienteService;

class ClienteWebController
{
    public function __construct(private ClienteService $service) {}
    
    public function index(): void
    {
        $clientes = $this->service->getAllClientes();
        $this->render('clientes/index', ['clientes' => $clientes]);
    }
    
    public function show(int $id): void
    {
        $cliente = $this->service->getCliente($id);
        
        if (!$cliente) {
            $this->redirect('/clientes', 'Cliente não encontrado', 'error');
            return;
        }
        
        $this->render('clientes/show', ['cliente' => $cliente]);
    }
    
    public function create(): void
    {
        $this->render('clientes/create');
    }
    
    public function store(): void
    {
        $result = $this->service->createCliente($_POST);
        
        if (!$result['success']) {
            $this->render('clientes/create', ['errors' => $result['errors'], 'data' => $_POST]);
            return;
        }
        
        $this->redirect('/clientes', 'Cliente criado com sucesso!', 'success');
    }
    
    public function edit(int $id): void
    {
        $cliente = $this->service->getCliente($id);
        
        if (!$cliente) {
            $this->redirect('/clientes', 'Cliente não encontrado', 'error');
            return;
        }
        
        $this->render('clientes/edit', ['cliente' => $cliente]);
    }
    
    public function update(int $id): void
    {
        $result = $this->service->updateCliente($id, $_POST);
        
        if (!$result['success']) {
            $cliente = $this->service->getCliente($id);
            $this->render('clientes/edit', ['cliente' => $cliente, 'errors' => $result['errors']]);
            return;
        }
        
        $this->redirect('/clientes', 'Cliente atualizado com sucesso!', 'success');
    }
    
    public function destroy(int $id): void
    {
        $success = $this->service->deleteCliente($id);
        $message = $success ? 'Cliente excluído com sucesso!' : 'Erro ao excluir cliente';
        $type = $success ? 'success' : 'error';
        
        $this->redirect('/clientes', $message, $type);
    }
    
    private function render(string $view, array $data = []): void
    {
        extract($data);
        include "views/{$view}.php";
    }
    
    private function redirect(string $url, string $message = '', string $type = 'info'): void
    {
        if ($message) {
            $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        }
        header("Location: {$url}");
        exit;
    }
}