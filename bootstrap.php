<?php
// Iniciar sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir constantes de caminho
define('ROOT_DIR', __DIR__);
define('CONFIG_DIR', ROOT_DIR . '/config');
define('CONTROLLERS_DIR', ROOT_DIR . '/controllers');
define('MODELS_DIR', ROOT_DIR . '/models');
define('VIEWS_DIR', ROOT_DIR . '/views');
define('HELPERS_DIR', ROOT_DIR . '/helpers');
define('ASSETS_DIR', ROOT_DIR . '/assets');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');

// Carregar arquivos de configuração
require_once CONFIG_DIR . '/config.php';

// Carregar funções auxiliares
require_once HELPERS_DIR . '/functions.php';

// Carregar conexão com o banco de dados
require_once CONFIG_DIR . '/database.php';

// Função de autoload para carregar classes automaticamente
spl_autoload_register(function ($class_name) {
    // Converter namespace para caminho de arquivo
    $class_path = str_replace('\\', '/', $class_name);
    
    // Verificar se é um controlador
    if (file_exists(CONTROLLERS_DIR . '/' . $class_path . '.php')) {
        require_once CONTROLLERS_DIR . '/' . $class_path . '.php';
        return;
    }
    
    // Verificar se é um modelo
    if (file_exists(MODELS_DIR . '/' . $class_path . '.php')) {
        require_once MODELS_DIR . '/' . $class_path . '.php';
        return;
    }
    
    // Verificar se é uma classe auxiliar
    if (file_exists(HELPERS_DIR . '/' . $class_path . '.php')) {
        require_once HELPERS_DIR . '/' . $class_path . '.php';
        return;
    }
});

// Definir constantes globais
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', UPLOADS_DIR . '/');
if (!defined('SITE_NAME')) define('SITE_NAME', 'Simão Refrigeração');

// Configurar tratamento de erros
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Função para lidar com erros fatais
function fatal_error_handler() {
    $error = error_get_last();
    if ($error !== null && $error['type'] === E_ERROR) {
        // Registrar o erro
        error_log("Erro fatal: " . $error['message'] . " em " . $error['file'] . " na linha " . $error['line']);
        
        // Exibir página de erro
        if (DEBUG_MODE) {
            echo "<h1>Erro Fatal</h1>";
            echo "<p>{$error['message']}</p>";
            echo "<p>Arquivo: {$error['file']}</p>";
            echo "<p>Linha: {$error['line']}</p>";
        } else {
            // Redirecionar para página de erro
            header('Location: /simaorefrigeracao/erro');
            exit;
        }
    }
}

// Registrar manipulador de erros fatais
register_shutdown_function('fatal_error_handler');
