<?php
/**
 * Arquivo de redirecionamento principal
 * Redireciona todas as requisições para a pasta public
 */

// Verificar se estamos em ambiente de desenvolvimento
$is_dev = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
           strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

// Se estamos em ambiente de produção, redirecionar para a pasta public
if (!$is_dev) {
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    $redirect_path = rtrim($base_path, '/') . '/public/';
    header("Location: $redirect_path");
    exit;
}

// Em ambiente de desenvolvimento, incluir o arquivo index.php da pasta public
require_once __DIR__ . '/public/index.php';