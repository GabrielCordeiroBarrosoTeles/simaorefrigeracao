<?php
// Configurações do site
define('SITE_NAME', 'Simão Refrigeração');
define('SITE_DESCRIPTION', 'Serviços de instalação, manutenção e projetos de ar condicionado para residências, comércios e indústrias.');
define('SITE_URL', 'http://localhost/simaorefrigeracao'); // Altere para o seu domínio

// Configurações de email
define('EMAIL_FROM', 'simaorefrigeracao2@gmail.com');
define('EMAIL_NAME', 'Simão Refrigeração');

// Configurações de upload
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configurações de sessão
define('SESSION_NAME', 'simaorefrigeracao_session');
define('SESSION_LIFETIME', 7200); // 2 horas

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de debug
define('DEBUG_MODE', true);

// Exibir erros apenas em modo de debug
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}
