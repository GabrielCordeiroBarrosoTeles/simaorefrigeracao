<?php
// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/config.php';
require_once 'config/database.php';

// Definir constantes se não existirem
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Simão Refrigeração');
}

// Conectar ao banco de dados
try {
    $db = db_connect();
    echo "<h2>Conexão com o banco de dados estabelecida com sucesso!</h2>";
} catch (PDOException $e) {
    die("<h2>Erro ao conectar ao banco de dados:</h2> <p>{$e->getMessage()}</p>");
}

// Verificar qual banco de dados está sendo usado
try {
    $query = "SELECT DATABASE() as db_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_db = $result['db_name'];
    
    echo "<h3>Banco de dados atual: {$current_db}</h3>";
} catch (PDOException $e) {
    echo "<p>Erro ao verificar o banco de dados atual: {$e->getMessage()}</p>";
}

// Verificar e criar tabelas necessárias
$tables = [
    'clientes' => "
        CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            endereco VARCHAR(255),
            cidade VARCHAR(100),
            estado VARCHAR(2),
            cep VARCHAR(10),
            tipo ENUM('residencial', 'comercial', 'industrial') DEFAULT 'residencial',
            observacoes TEXT,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'tecnicos' => "
        CREATE TABLE IF NOT EXISTS tecnicos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            especialidade VARCHAR(100),
            cor VARCHAR(20) DEFAULT '#3b82f6',
            status ENUM('ativo', 'inativo') DEFAULT 'ativo',
            usuario_id INT,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'servicos' => "
        CREATE TABLE IF NOT EXISTS servicos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(100) NOT NULL,
            icone VARCHAR(50),
            descricao TEXT NOT NULL,
            itens TEXT,
            preco DECIMAL(10,2),
            destaque TINYINT(1) DEFAULT 0,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'agendamentos' => "
        CREATE TABLE IF NOT EXISTS agendamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(100) NOT NULL,
            cliente_id INT NOT NULL,
            servico_id INT NOT NULL,
            tecnico_id INT,
            data_agendamento DATE NOT NULL,
            hora_inicio TIME NOT NULL,
            hora_fim TIME,
            observacoes TEXT,
            status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') DEFAULT 'pendente',
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
            FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'depoimentos' => "
        CREATE TABLE IF NOT EXISTS depoimentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            cargo VARCHAR(100),
            empresa VARCHAR(100),
            texto TEXT NOT NULL,
            avaliacao INT DEFAULT 5,
            status ENUM('ativo', 'inativo') DEFAULT 'ativo',
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'contatos' => "
        CREATE TABLE IF NOT EXISTS contatos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            assunto VARCHAR(100),
            mensagem TEXT NOT NULL,
            status ENUM('novo', 'lido', 'respondido', 'arquivado') DEFAULT 'novo',
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    "
];

// Verificar e criar cada tabela
echo "<h3>Verificando e criando tabelas:</h3>";
echo "<ul>";

foreach ($tables as $table => $sql) {
    try {
        // Verificar se a tabela existe
        $query = "SHOW TABLES LIKE '{$table}'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            echo "<li>Tabela <strong>{$table}</strong>: Já existe</li>";
        } else {
            // Criar a tabela
            $stmt = $db->prepare($sql);
            $stmt->execute();
            echo "<li>Tabela <strong>{$table}</strong>: Criada com sucesso</li>";
        }
    } catch (PDOException $e) {
        echo "<li>Erro ao verificar/criar tabela <strong>{$table}</strong>: {$e->getMessage()}</li>";
    }
}

echo "</ul>";

// Verificar se a tabela usuarios existe
try {
    $query = "SHOW TABLES LIKE 'usuarios'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "<p>Tabela <strong>usuarios</strong>: Já existe</p>";
        
        // Verificar se há pelo menos um usuário admin
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE nivel = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] == 0) {
            // Criar usuário admin padrão
            $nome = 'Administrador';
            $email = 'admin@admin.com';
            $senha = password_hash('admin123', PASSWORD_DEFAULT);
            $nivel = 'admin';
            
            $query = "INSERT INTO usuarios (nome, email, senha, nivel, data_criacao) 
                      VALUES (:nome, :email, :senha, :nivel, NOW())";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senha);
            $stmt->bindParam(':nivel', $nivel);
            $stmt->execute();
            
            echo "<p>Usuário admin padrão criado: Email: admin@admin.com, Senha: admin123</p>";
        }
    } else {
        // Criar a tabela usuarios
        $sql = "
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                nivel ENUM('admin', 'tecnico', 'tecnico_adm') DEFAULT 'admin',
                ultimo_login DATETIME,
                data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                data_atualizacao DATETIME ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        
        // Criar usuário admin padrão
        $nome = 'Administrador';
        $email = 'admin@admin.com';
        $senha = password_hash('admin123', PASSWORD_DEFAULT);
        $nivel = 'admin';
        
        $query = "INSERT INTO usuarios (nome, email, senha, nivel, data_criacao) 
                  VALUES (:nome, :email, :senha, :nivel, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':nivel', $nivel);
        $stmt->execute();
        
        echo "<p>Tabela <strong>usuarios</strong>: Criada com sucesso</p>";
        echo "<p>Usuário admin padrão criado: Email: admin@admin.com, Senha: admin123</p>";
    }
} catch (PDOException $e) {
    echo "<p>Erro ao verificar/criar tabela <strong>usuarios</strong>: {$e->getMessage()}</p>";
}

// Criar funções auxiliares se não existirem
echo "<h3>Verificando funções auxiliares:</h3>";

// Verificar se a função format_date existe
if (!function_exists('format_date')) {
    echo "<p>Função format_date não existe. Adicionando...</p>";
    
    function format_date($date, $format = 'd/m/Y') {
        return date($format, strtotime($date));
    }
}

// Verificar se a função set_flash_message existe
if (!function_exists('set_flash_message')) {
    echo "<p>Função set_flash_message não existe. Adicionando...</p>";
    
    function set_flash_message($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

// Verificar se a função get_flash_message existe
if (!function_exists('get_flash_message')) {
    echo "<p>Função get_flash_message não existe. Adicionando...</p>";
    
    function get_flash_message() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}

// Verificar se a função generate_csrf_token existe
if (!function_exists('generate_csrf_token')) {
    echo "<p>Função generate_csrf_token não existe. Adicionando...</p>";
    
    function generate_csrf_token() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Verificar se a função verify_csrf_token existe
if (!function_exists('verify_csrf_token')) {
    echo "<p>Função verify_csrf_token não existe. Adicionando...</p>";
    
    function verify_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Verificar se a função is_logged_in existe
if (!function_exists('is_logged_in')) {
    echo "<p>Função is_logged_in não existe. Adicionando...</p>";
    
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

// Verificar se a função user_has_access existe
if (!function_exists('user_has_access')) {
    echo "<p>Função user_has_access não existe. Adicionando...</p>";
    
    function user_has_access($allowed_levels = ['admin']) {
        if (!is_logged_in()) {
            return false;
        }
        
        $user_level = $_SESSION['user_nivel'] ?? '';
        
        if (is_array($allowed_levels)) {
            return in_array($user_level, $allowed_levels);
        } else {
            return $user_level === $allowed_levels;
        }
    }
}

// Verificar se a função sanitize existe
if (!function_exists('sanitize')) {
    echo "<p>Função sanitize não existe. Adicionando...</p>";
    
    function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// Verificar se a função redirect existe
if (!function_exists('redirect')) {
    echo "<p>Função redirect não existe. Adicionando...</p>";
    
    function redirect($path) {
        header("Location: {$path}");
        exit;
    }
}

// Criar arquivo de logout corrigido
$logout_file = 'logout.php';
$logout_content = '<?php
// Iniciar sessão
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Se houver um cookie de sessão, destruí-lo também
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: admin-login.php?logout=" . time());
exit;
';

file_put_contents($logout_file, $logout_content);
echo "<p>Arquivo de logout corrigido criado: {$logout_file}</p>";

// Criar arquivo de funções auxiliares
$functions_file = 'helpers/functions.php';
$functions_content = '<?php
// Funções de utilidade

// Verificar se o usuário está logado
function is_logged_in() {
    return isset($_SESSION["user_id"]);
}

// Verificar se o usuário tem nível de acesso adequado
function user_has_access($allowed_levels = ["admin"]) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_level = $_SESSION["user_nivel"] ?? "";
    
    if (is_array($allowed_levels)) {
        return in_array($user_level, $allowed_levels);
    } else {
        return $user_level === $allowed_levels;
    }
}

// Redirecionar para outra página
function redirect($path) {
    header("Location: " . $path);
    exit;
}

// Sanitizar entrada do usuário
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, "UTF-8");
}

// Gerar token CSRF
function generate_csrf_token() {
    if (!isset($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

// Verificar token CSRF
function verify_csrf_token($token) {
    return isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token);
}

// Definir mensagem flash
function set_flash_message($type, $message) {
    $_SESSION["flash_message"] = [
        "type" => $type,
        "message" => $message
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
    if (isset($_SESSION["flash_message"])) {
        $type = $_SESSION["flash_message"]["type"];
        $message = $_SESSION["flash_message"]["message"];
        
        echo "<div class=\"alert alert-" . $type . " alert-dismissible fade show\" role=\"alert\">";
        echo $message;
        echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">";
        echo "<span aria-hidden=\"true\">&times;</span>";
        echo "</button>";
        echo "</div>";
        
        // Limpar a mensagem flash após exibi-la
        unset($_SESSION["flash_message"]);
    }
}

// Formatar data para exibição
function format_date($date, $format = "d/m/Y") {
    return date($format, strtotime($date));
}

// Formatar valor monetário
function format_money($value) {
    return "R$ " . number_format($value, 2, ",", ".");
}

// Truncar texto
function truncate($text, $length = 100, $append = "...") {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, " "));
    
    return $text . $append;
}

// Verificar se é uma requisição POST
function is_post_request() {
    return $_SERVER["REQUEST_METHOD"] === "POST";
}

// Verificar se é uma requisição AJAX
function is_ajax_request() {
    return !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest";
}
';

// Criar diretório helpers se não existir
if (!file_exists('helpers')) {
    mkdir('helpers', 0777, true);
}

file_put_contents($functions_file, $functions_content);
echo "<p>Arquivo de funções auxiliares criado: {$functions_file}</p>";

echo "<h3>Correções concluídas!</h3>";
echo "<p><a href='admin-dashboard.php' class='btn btn-primary'>Voltar para o Dashboard</a></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    h2, h3 {
        color: #2563eb;
    }
    ul {
        margin-bottom: 20px;
    }
    li {
        margin-bottom: 5px;
    }
    p {
        margin-bottom: 10px;
    }
    .btn {
        display: inline-block;
        background-color: #2563eb;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 20px;
    }
    .btn:hover {
        background-color: #1d4ed8;
    }
</style>
