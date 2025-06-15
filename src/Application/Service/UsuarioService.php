<?php
namespace App\Service;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;

class UsuarioService
{
    private UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function listarTodos(): array
    {
        return $this->usuarioRepository->findAll();
    }

    public function buscarPorId(int $id): ?Usuario
    {
        return $this->usuarioRepository->findById($id);
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        return $this->usuarioRepository->findByEmail($email);
    }

    public function buscarPorNivel(string $nivel): array
    {
        return $this->usuarioRepository->findByNivel($nivel);
    }

    public function autenticar(string $email, string $senha): ?Usuario
    {
        return $this->usuarioRepository->autenticar($email, $senha);
    }

    public function criar(array $dados): ?Usuario
    {
        // Validar dados obrigatórios
        if (!isset($dados['nome']) || !isset($dados['email']) || !isset($dados['senha'])) {
            return null;
        }
        
        // Verificar se já existe um usuário com o mesmo email
        if ($this->usuarioRepository->findByEmail($dados['email'])) {
            return null;
        }
        
        $usuario = new Usuario();
        $usuario->setNome($dados['nome']);
        $usuario->setEmail($dados['email']);
        $usuario->setSenha(password_hash($dados['senha'], PASSWORD_DEFAULT));
        
        if (isset($dados['nivel'])) {
            $usuario->setNivel($dados['nivel']);
        }
        
        $this->usuarioRepository->save($usuario);
        return $usuario;
    }

    public function atualizar(Usuario $usuario, array $dados): Usuario
    {
        if (isset($dados['nome'])) {
            $usuario->setNome($dados['nome']);
        }
        
        if (isset($dados['email'])) {
            // Verificar se o novo email já está em uso por outro usuário
            $existente = $this->usuarioRepository->findByEmail($dados['email']);
            if (!$existente || $existente->getId() === $usuario->getId()) {
                $usuario->setEmail($dados['email']);
            }
        }
        
        if (isset($dados['senha']) && !empty($dados['senha'])) {
            $usuario->setSenha(password_hash($dados['senha'], PASSWORD_DEFAULT));
        }
        
        if (isset($dados['nivel'])) {
            $usuario->setNivel($dados['nivel']);
        }
        
        $this->usuarioRepository->save($usuario);
        return $usuario;
    }

    public function excluir(Usuario $usuario): void
    {
        $this->usuarioRepository->remove($usuario);
    }

    public function atualizarUltimoLogin(Usuario $usuario): void
    {
        $usuario->setUltimoLogin();
        $this->usuarioRepository->save($usuario);
    }
}