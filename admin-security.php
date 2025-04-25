<?php
// Iniciar sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Redirecionar para a página de login
    set_flash_message('danger', 'Você precisa fazer login para acessar esta página.');
    header('Location: admin-login.php');
    exit;
}

// Verificar timeout de sessão (30 minutos)
$session_timeout = 30 * 60; // 30 minutos em segundos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Sessão expirada
    session_unset();
    session_destroy();
    set_flash_message('warning', 'Sua sessão expirou. Por favor, faça login novamente.');
    header('Location: admin-login.php?timeout=1');
    exit;
}

// Atualizar último acesso
$_SESSION['last_activity'] = time();

// Verificar se o usuário existe no banco de dados
try {
    $db = db_connect();
    $query = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Usuário não encontrado no banco de dados
        session_unset();
        session_destroy();
        set_flash_message('danger', 'Usuário não encontrado. Por favor, faça login novamente.');
        header('Location: admin-login.php');
        exit;
    }
} catch (PDOException $e) {
    // Erro ao verificar usuário
    if (DEBUG_MODE) {
        $_SESSION['error_details'] = $e->getMessage();
    }
}

// Processar logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: admin-login.php');
    exit;
}
