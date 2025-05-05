<?php
// Iniciar a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Depuração - mostrar informações da sessão antes de destruí-la
$debug = false;
if ($debug) {
    echo "<pre>";
    echo "Session ID antes: " . session_id() . "\n";
    echo "Variáveis de sessão antes:\n";
    print_r($_SESSION);
    echo "</pre>";
}

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie da sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir a sessão
session_destroy();

// Depuração - verificar se a sessão foi destruída
if ($debug) {
    // Iniciar uma nova sessão para verificar
    session_start();
    echo "<pre>";
    echo "Session ID depois: " . session_id() . "\n";
    echo "Variáveis de sessão depois:\n";
    print_r($_SESSION);
    echo "</pre>";
    echo "<a href='admin-login.php'>Ir para login</a>";
    exit;
}

// Redirecionar para a página de login com um parâmetro de timestamp para evitar cache
header("Location: admin-login.php?logout=" . time());
exit;
