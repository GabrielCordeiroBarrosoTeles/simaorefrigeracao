<?php
namespace App\Controller;

use App\Service\UsuarioService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UsuarioRepository;

class AuthController
{
    private UsuarioService $usuarioService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $usuarioRepository = new UsuarioRepository($entityManager);
        $this->usuarioService = new UsuarioService($usuarioRepository);
    }

    public function login(string $email, string $senha): ?array
    {
        $usuario = $this->usuarioService->autenticar($email, $senha);
        
        if (!$usuario) {
            return null;
        }
        
        // Iniciar sessão
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['usuario_id'] = $usuario->getId();
        $_SESSION['usuario_nome'] = $usuario->getNome();
        $_SESSION['usuario_email'] = $usuario->getEmail();
        $_SESSION['usuario_nivel'] = $usuario->getNivel();
        $_SESSION['logado'] = true;
        
        return [
            'id' => $usuario->getId(),
            'nome' => $usuario->getNome(),
            'email' => $usuario->getEmail(),
            'nivel' => $usuario->getNivel(),
            'ultimo_login' => $usuario->getUltimoLogin() ? $usuario->getUltimoLogin()->format('Y-m-d H:i:s') : null
        ];
    }

    public function logout(): bool
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Limpar todas as variáveis de sessão
        $_SESSION = [];
        
        // Destruir a sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destruir a sessão
        session_destroy();
        
        return true;
    }

    public function verificarAutenticacao(): bool
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
    }

    public function verificarNivel(string $nivel): bool
    {
        if (!$this->verificarAutenticacao()) {
            return false;
        }
        
        if ($nivel === 'admin' && $_SESSION['usuario_nivel'] === 'admin') {
            return true;
        }
        
        if ($nivel === 'tecnico' && ($_SESSION['usuario_nivel'] === 'tecnico' || $_SESSION['usuario_nivel'] === 'tecnico_adm' || $_SESSION['usuario_nivel'] === 'admin')) {
            return true;
        }
        
        if ($nivel === 'editor' && ($_SESSION['usuario_nivel'] === 'editor' || $_SESSION['usuario_nivel'] === 'admin')) {
            return true;
        }
        
        return false;
    }

    public function getUsuarioLogado(): ?array
    {
        if (!$this->verificarAutenticacao()) {
            return null;
        }
        
        $usuario = $this->usuarioService->buscarPorId($_SESSION['usuario_id']);
        
        if (!$usuario) {
            $this->logout();
            return null;
        }
        
        return [
            'id' => $usuario->getId(),
            'nome' => $usuario->getNome(),
            'email' => $usuario->getEmail(),
            'nivel' => $usuario->getNivel()
        ];
    }
}