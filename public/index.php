<?php
// Iniciar sessão
session_start();

// Definir constantes de caminho
define('ROOT_DIR', dirname(__DIR__));
define('SRC_DIR', ROOT_DIR . '/src');
define('CONFIG_DIR', SRC_DIR . '/config');
define('HELPERS_DIR', SRC_DIR . '/helpers');
define('VIEWS_DIR', SRC_DIR . '/views');
define('ADMIN_DIR', SRC_DIR . '/admin');
define('TECNICO_DIR', SRC_DIR . '/tecnico');
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('ASSETS_DIR', PUBLIC_DIR . '/assets');

// Incluir arquivos necessários
require_once CONFIG_DIR . '/config.php';
require_once CONFIG_DIR . '/database.php';
require_once HELPERS_DIR . '/functions.php';

// Obter a URL solicitada
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/simaorefrigeracao'; // Ajuste conforme necessário

// Remover o caminho base e parâmetros de consulta
$request_uri = str_replace($base_path, '', $request_uri);
$request_uri = strtok($request_uri, '?');

// Verificar se é um arquivo de assets
if (strpos($request_uri, '/assets/') === 0) {
    $file_path = PUBLIC_DIR . $request_uri;
    if (file_exists($file_path)) {
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'js':
                header('Content-Type: application/javascript');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
        }
        readfile($file_path);
        exit;
    }
}

// Roteamento básico
if ($request_uri === '/admin-login.php') {
    // Página de login especial
    require_once SRC_DIR . '/admin/login.php';
} elseif (strpos($request_uri, '/admin') === 0) {
    // Rota administrativa
    $path = str_replace('/admin', '', $request_uri);
    $path = $path ? $path : '/dashboard';
    $file = ADMIN_DIR . $path . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    } else {
        require_once ADMIN_DIR . '/dashboard.php';
    }
} elseif (strpos($request_uri, '/tecnico') === 0) {
    // Rota do técnico
    $path = str_replace('/tecnico', '', $request_uri);
    $path = $path ? $path : '/dashboard';
    $file = TECNICO_DIR . $path . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    } else {
        require_once TECNICO_DIR . '/dashboard.php';
    }
} else {
    // Rota pública
    if ($request_uri === '/' || $request_uri === '') {
        require_once SRC_DIR . '/index.php';
    } else {
        $file = SRC_DIR . $request_uri;
        if (file_exists($file)) {
            require_once $file;
        } else {
            require_once SRC_DIR . '/index.php';
        }
    }
}