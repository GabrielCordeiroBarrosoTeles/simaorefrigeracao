<?php
// Funções de utilidade

// Verificar se o usuário está logado
function is_logged_in() {
    // Iniciar sessão se ainda não estiver iniciada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']);
}

// Verificar se o usuário tem nível de acesso adequado
function has_permission($required_level = 'admin') {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_level = $_SESSION['user_nivel'] ?? '';
    
    if ($required_level === 'admin' && $user_level === 'admin') {
        return true;
    }
    
    if ($required_level === 'tecnico' && ($user_level === 'admin' || $user_level === 'tecnico')) {
        return true;
    }
    
    return false;
}

// Redirecionar para outra página
function redirect($path) {
    header('Location: /simaorefrigeracao' . $path);
    exit;
}

// Sanitizar entrada do usuário
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Gerar token CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar token CSRF
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Definir mensagem flash
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Obter mensagem flash
function get_flash_message() {
    if (isset($_SESSION["flash_message"])) {
        $message = $_SESSION["flash_message"];
        unset($_SESSION["flash_message"]);
        return $message;
    }
    return null;
}

// Exibir mensagem flash
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'];
        $message = $_SESSION['flash_message']['message'];
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
        
        // Limpar a mensagem flash após exibi-la
        unset($_SESSION['flash_message']);
    }
}

// Formatar data para exibição
function format_date($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Formatar valor monetário
function format_money($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

// Truncar texto
function truncate($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

// Verificar se é uma requisição POST
function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Verificar se é uma requisição AJAX
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Gerar slug a partir de um texto
function generate_slug($text) {
    // Converter para minúsculas
    $text = strtolower($text);
    
    // Remover caracteres especiais
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Substituir espaços por hífens
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Remover hífens do início e do fim
    $text = trim($text, '-');
    
    return $text;
}

// Fazer upload de arquivo
function upload_file($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Erro no upload do arquivo.'
        ];
    }
    
    // Verificar tipo de arquivo
    if (!in_array($file['type'], $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Tipo de arquivo não permitido.'
        ];
    }
    
    // Verificar tamanho do arquivo
    if ($max_size === null) {
        $max_size = 5 * 1024 * 1024; // 5MB por padrão
    }
    
    if ($file['size'] > $max_size) {
        return [
            'success' => false,
            'message' => 'O arquivo é muito grande. Tamanho máximo: ' . ($max_size / 1024 / 1024) . 'MB.'
        ];
    }
    
    // Criar diretório de upload se não existir
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Gerar nome único para o arquivo
    $filename = uniqid() . '_' . basename($file['name']);
    $upload_path = $upload_dir . $filename;
    
    // Mover o arquivo para o diretório de upload
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $upload_path
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Erro ao mover o arquivo.'
        ];
    }
}
