<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

class ApiController
{
    protected EntityManagerInterface $entityManager;
    protected ClienteController $clienteController;
    protected AgendamentoController $agendamentoController;
    protected AuthController $authController;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->clienteController = new ClienteController($entityManager);
        $this->agendamentoController = new AgendamentoController($entityManager);
        $this->authController = new AuthController($entityManager);
    }

    public function processarRequisicao(string $rota, string $metodo, array $dados = []): array
    {
        // Verificar autenticação para rotas protegidas
        if ($this->rotaProtegida($rota) && !$this->authController->verificarAutenticacao()) {
            return $this->responderErro('Não autorizado', 401);
        }

        // Processar a rota
        switch ($rota) {
            // Rotas de autenticação
            case 'login':
                if ($metodo !== 'POST') {
                    return $this->responderErro('Método não permitido', 405);
                }
                
                if (!isset($dados['email']) || !isset($dados['senha'])) {
                    return $this->responderErro('Dados incompletos', 400);
                }
                
                $resultado = $this->authController->login($dados['email'], $dados['senha']);
                
                if (!$resultado) {
                    return $this->responderErro('Credenciais inválidas', 401);
                }
                
                return $this->responderSucesso($resultado);
                
            case 'logout':
                if ($metodo !== 'POST') {
                    return $this->responderErro('Método não permitido', 405);
                }
                
                $this->authController->logout();
                return $this->responderSucesso(['mensagem' => 'Logout realizado com sucesso']);
                
            // Rotas de clientes
            case 'clientes':
                if ($metodo === 'GET') {
                    return $this->responderSucesso($this->clienteController->index());
                } elseif ($metodo === 'POST') {
                    $resultado = $this->clienteController->create($dados);
                    
                    if (!$resultado) {
                        return $this->responderErro('Erro ao criar cliente', 400);
                    }
                    
                    return $this->responderSucesso($resultado, 201);
                }
                
                return $this->responderErro('Método não permitido', 405);
                
            case (preg_match('/^clientes\/(\d+)$/', $rota, $matches) ? true : false):
                $id = (int) $matches[1];
                
                if ($metodo === 'GET') {
                    $resultado = $this->clienteController->show($id);
                    
                    if (!$resultado) {
                        return $this->responderErro('Cliente não encontrado', 404);
                    }
                    
                    return $this->responderSucesso($resultado);
                } elseif ($metodo === 'PUT') {
                    $resultado = $this->clienteController->update($id, $dados);
                    
                    if (!$resultado) {
                        return $this->responderErro('Cliente não encontrado', 404);
                    }
                    
                    return $this->responderSucesso($resultado);
                } elseif ($metodo === 'DELETE') {
                    $resultado = $this->clienteController->delete($id);
                    
                    if (!$resultado) {
                        return $this->responderErro('Cliente não encontrado', 404);
                    }
                    
                    return $this->responderSucesso(['mensagem' => 'Cliente excluído com sucesso']);
                }
                
                return $this->responderErro('Método não permitido', 405);
                
            // Rotas de agendamentos
            case 'agendamentos':
                if ($metodo === 'GET') {
                    return $this->responderSucesso($this->agendamentoController->index());
                } elseif ($metodo === 'POST') {
                    $resultado = $this->agendamentoController->create($dados);
                    
                    if (!$resultado) {
                        return $this->responderErro('Erro ao criar agendamento', 400);
                    }
                    
                    return $this->responderSucesso($resultado, 201);
                }
                
                return $this->responderErro('Método não permitido', 405);
                
            case (preg_match('/^agendamentos\/(\d+)$/', $rota, $matches) ? true : false):
                $id = (int) $matches[1];
                
                if ($metodo === 'GET') {
                    $resultado = $this->agendamentoController->show($id);
                    
                    if (!$resultado) {
                        return $this->responderErro('Agendamento não encontrado', 404);
                    }
                    
                    return $this->responderSucesso($resultado);
                } elseif ($metodo === 'PUT') {
                    $resultado = $this->agendamentoController->update($id, $dados);
                    
                    if (!$resultado) {
                        return $this->responderErro('Agendamento não encontrado', 404);
                    }
                    
                    return $this->responderSucesso($resultado);
                } elseif ($metodo === 'DELETE') {
                    $resultado = $this->agendamentoController->delete($id);
                    
                    if (!$resultado) {
                        return $this->responderErro('Agendamento não encontrado', 404);
                    }
                    
                    return $this->responderSucesso(['mensagem' => 'Agendamento excluído com sucesso']);
                }
                
                return $this->responderErro('Método não permitido', 405);
                
            case (preg_match('/^agendamentos\/cliente\/(\d+)$/', $rota, $matches) ? true : false):
                $clienteId = (int) $matches[1];
                
                if ($metodo === 'GET') {
                    return $this->responderSucesso($this->agendamentoController->findByCliente($clienteId));
                }
                
                return $this->responderErro('Método não permitido', 405);
                
            case (preg_match('/^agendamentos\/tecnico\/(\d+)$/', $rota, $matches) ? true : false):
                $tecnicoId = (int) $matches[1];
                
                if ($metodo === 'GET') {
                    return $this->responderSucesso($this->agendamentoController->findByTecnico($tecnicoId));
                }
                
                return $this->responderErro('Método não permitido', 405);
                
            default:
                return $this->responderErro('Rota não encontrada', 404);
        }
    }

    protected function rotaProtegida(string $rota): bool
    {
        // Rotas que não precisam de autenticação
        $rotasPublicas = [
            'login'
        ];
        
        return !in_array($rota, $rotasPublicas);
    }

    protected function responderSucesso(array $dados, int $status = 200): array
    {
        return [
            'status' => $status,
            'sucesso' => true,
            'dados' => $dados
        ];
    }

    protected function responderErro(string $mensagem, int $status = 400): array
    {
        return [
            'status' => $status,
            'sucesso' => false,
            'erro' => $mensagem
        ];
    }
}