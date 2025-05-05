<?php
// Iniciar sessão
session_start();

// Definir constantes se não existirem
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Simão Refrigeração');
}

// Função para exibir mensagem
function show_message($type, $message) {
    echo "<div class='alert alert-{$type}'>{$message}</div>";
}

// Verificar conexão com o banco de dados
echo "<h2>1. Verificando conexão com o banco de dados</h2>";

// Incluir arquivo de configuração do banco de dados
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    show_message('success', 'Arquivo config/database.php encontrado.');
} else {
    show_message('danger', 'Arquivo config/database.php não encontrado.');
    exit;
}

// Tentar conectar ao banco de dados
try {
    $db = db_connect();
    show_message('success', 'Conexão com o banco de dados estabelecida com sucesso!');
    
    // Verificar qual banco de dados está sendo usado
    $query = "SELECT DATABASE() as db_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_db = $result['db_name'];
    
    show_message('info', "Banco de dados atual: {$current_db}");
} catch (PDOException $e) {
    show_message('danger', 'Erro ao conectar ao banco de dados: ' . $e->getMessage());
    exit;
}

// Verificar e criar tabelas necessárias
echo "<h2>2. Verificando e criando tabelas necessárias</h2>";

$tables = [
    'usuarios' => "
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
    ",
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

// Verificar se há pelo menos um usuário admin
try {
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
        
        show_message('success', "Usuário admin padrão criado: Email: admin@admin.com, Senha: admin123");
    } else {
        show_message('info', "Já existe pelo menos um usuário admin no sistema.");
    }
} catch (PDOException $e) {
    show_message('danger', "Erro ao verificar/criar usuário admin: " . $e->getMessage());
}

// Corrigir arquivo de funções
echo "<h2>3. Corrigindo arquivo de funções</h2>";

// Criar diretório helpers se não existir
if (!file_exists('helpers')) {
    mkdir('helpers', 0777, true);
    show_message('success', "Diretório helpers criado com sucesso.");
}

// Criar arquivo de funções
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

file_put_contents($functions_file, $functions_content);
show_message('success', "Arquivo de funções criado/atualizado: {$functions_file}");

// Corrigir arquivo de logout
echo "<h2>4. Corrigindo arquivo de logout</h2>";

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
show_message('success', "Arquivo de logout criado/atualizado: {$logout_file}");

// Verificar links de logout no admin-dashboard.php
$dashboard_file = 'admin-dashboard.php';
if (file_exists($dashboard_file)) {
    $dashboard_content = file_get_contents($dashboard_file);
    
    // Verificar se há links incorretos para logout
    if (strpos($dashboard_content, 'admin-dashboard.php?logout=1') !== false) {
        // Corrigir links de logout
        $dashboard_content = str_replace('admin-dashboard.php?logout=1', 'logout.php', $dashboard_content);
        file_put_contents($dashboard_file, $dashboard_content);
        show_message('success', "Links de logout corrigidos no arquivo {$dashboard_file}");
    } else {
        show_message('info', "Não foram encontrados links de logout incorretos no arquivo {$dashboard_file}");
    }
} else {
    show_message('warning', "Arquivo {$dashboard_file} não encontrado");
}

// Criar arquivo de teste de logout
echo "<h2>5. Criando arquivo de teste de logout</h2>";

$test_logout_file = 'test_logout.php';
$test_logout_content = '<?php
// Iniciar sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["user_id"])) {
    echo "<h2>Você não está logado.</h2>";
    echo "<p><a href=\"admin-login.php\" class=\"btn\">Ir para a página de login</a></p>";
    exit;
}

// Exibir informações da sessão atual
echo "<h2>Informações da sessão atual:</h2>";
echo "<ul>";
echo "<li><strong>ID do usuário:</strong> " . $_SESSION["user_id"] . "</li>";
echo "<li><strong>Nome:</strong> " . ($_SESSION["user_nome"] ?? "Não definido") . "</li>";
echo "<li><strong>Email:</strong> " . ($_SESSION["user_email"] ?? "Não definido") . "</li>";
echo "<li><strong>Nível:</strong> " . ($_SESSION["user_nivel"] ?? "Não definido") . "</li>";
echo "</ul>";

// Botão para testar o logout
echo "<h3>Testar logout:</h3>";
echo "<form action=\"logout.php\" method=\"post\">";
echo "<button type=\"submit\" class=\"btn\">Fazer logout</button>";
echo "</form>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        padding: 20px;
        max-width: 800px;
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
        border: none;
        cursor: pointer;
    }
    .btn:hover {
        background-color: #1d4ed8;
    }
</style>
';

file_put_contents($test_logout_file, $test_logout_content);
show_message('success', "Arquivo de teste de logout criado: {$test_logout_file}");

echo "<h2>Correções concluídas!</h2>";
echo "<p>Agora você pode:</p>";
echo "<ul>";
echo "<li><a href='admin-login.php'>Ir para a página de login</a></li>";
echo "<li><a href='test_logout.php'>Testar o logout</a></li>";
echo "</ul>";
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
    h2 {
        color: #2563eb;
        margin-top: 30px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }
    ul {
        margin-bottom: 20px;
    }
    li {
        margin-bottom: 5px;
    }
    .alert {
        padding: 12px 20px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    .alert-success {
        background-color: #d1fae5;
        border-left: 4px solid #10b981;
        color: #065f46;
    }
    .alert-info {
        background-color: #dbeafe;
        border-left: 4px solid #3b82f6;
        color: #1e40af;
    }
    .alert-warning {
        background-color: #fef3c7;
        border-left: 4px solid #f59e0b;
        color: #92400e;
    }
    .alert-danger {
        background-color: #fee2e2;
        border-left: 4px solid #ef4444;
        color: #b91c1c;
    }
    a {
        color: #2563eb;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
