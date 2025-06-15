<?php
// Iniciar sessão
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = [];

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header('Location: admin-login.php');
exit;
?>