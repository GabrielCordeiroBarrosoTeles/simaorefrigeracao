<?php
// Função para truncar texto
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Função para verificar se o usuário está logado
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Função para definir mensagem flash
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Função para obter mensagem flash
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $flash_message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash_message;
    }
    return null;
}

// Função para exibir mensagem flash
function display_flash_message() {
    $flash_message = get_flash_message();
    if ($flash_message) {
        echo '<div class="alert alert-' . $flash_message['type'] . ' alert-dismissible fade show" role="alert">';
        echo $flash_message['message'];
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Fechar">';
        echo '<span aria-hidden="true">&times;</span>';
        echo '</button>';
        echo '</div>';
    }
}

// Função para formatar data
function format_date($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

// Função para sanitizar entrada
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

// Função para redirecionar
function redirect($url) {
    header("Location: $url");
    exit;
}