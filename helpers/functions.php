<?php
// Função para redirecionar
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Função para exibir mensagens flash
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash_message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash_message;
    }
    return null;
}

// Função para sanitizar input
function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
    } else {
        $input = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

// Função para verificar se o usuário está logado
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Função para verificar se é uma requisição POST
function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Função para verificar se é uma requisição AJAX
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Função para gerar token CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para verificar token CSRF
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Função para formatar data
function format_date($date, $format = 'd/m/Y H:i') {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

// Função para truncar texto
function truncate($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length) . $append;
    }
    return $text;
}

// Função para gerar slug
function slugify($text) {
    // Substituir caracteres não alfanuméricos por hífen
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterar
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Remover caracteres indesejados
    $text = preg_replace('~[^-\w]+~', '', $text);
    // Trim
    $text = trim($text, '-');
    // Remover hífens duplicados
    $text = preg_replace('~-+~', '-', $text);
    // Converter para minúsculas
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

// Função para upload de arquivo
function upload_file($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Erro no upload do arquivo.'
        ];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Tipo de arquivo não permitido.'
        ];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return [
            'success' => false,
            'message' => 'O arquivo é muito grande. Tamanho máximo: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.'
        ];
    }
    
    $upload_dir = UPLOAD_DIR;
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $upload_path = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $upload_path
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Falha ao mover o arquivo enviado.'
        ];
    }
}
