<?php
// Iniciar a sessão
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie da sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: admin-login.php");
exit;
