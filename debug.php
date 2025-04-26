<?php
// Arquivo de depuração para ajudar a identificar problemas

// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para verificar se um arquivo existe
function check_file_exists($file_path) {
    if (file_exists($file_path)) {
        echo "<p style='color:green'>✓ O arquivo $file_path existe.</p>";
    } else {
        echo "<p style='color:red'>✗ O arquivo $file_path NÃO existe!</p>";
    }
}

// Verificar arquivos de controladores
echo "<h2>Verificando arquivos de controladores:</h2>";
check_file_exists('controllers/Admin/ClientesController.php');
check_file_exists('controllers/Admin/AgendamentosController.php');
check_file_exists('controllers/Admin/TecnicosController.php');
check_file_exists('controllers/Admin/DashboardController.php');
check_file_exists('controllers/Admin/AuthController.php');

// Verificar arquivos de visualização
echo "<h2>Verificando arquivos de visualização:</h2>";
check_file_exists('views/admin/clientes/index.php');
check_file_exists('views/admin/agendamentos/index.php');
check_file_exists('views/admin/tecnicos/index.php');
check_file_exists('views/admin/dashboard.php');
check_file_exists('views/admin/login.php');

// Verificar arquivos de configuração
echo "<h2>Verificando arquivos de configuração:</h2>";
check_file_exists('config/config.php');
check_file_exists('config/database.php');
check_file_exists('helpers/functions.php');

// Verificar conexão com o banco de dados
echo "<h2>Verificando conexão com o banco de dados:</h2>";
try {
    require_once 'config/database.php';
    $db = db_connect();
    echo "<p style='color:green'>✓ Conexão com o banco de dados estabelecida com sucesso.</p>";
    
    // Verificar tabelas
    $tables = ['usuarios', 'clientes', 'tecnicos', 'agendamentos', 'servicos'];
    echo "<h3>Verificando tabelas:</h3>";
    foreach ($tables as $table) {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            echo "<p style='color:green'>✓ Tabela $table existe.</p>";
            
            // Verificar registros
            $query = "SELECT COUNT(*) as total FROM $table";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "<p style='margin-left:20px'>- Contém $count registros.</p>";
        } else {
            echo "<p style='color:red'>✗ Tabela $table NÃO existe!</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
}

// Verificar sessão
echo "<h2>Verificando sessão:</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color:green'>✓ Sessão está ativa.</p>";
    
    if (isset($_SESSION['user_id'])) {
        echo "<p style='color:green'>✓ Usuário está logado (ID: " . $_SESSION['user_id'] . ").</p>";
        echo "<p>Nome: " . ($_SESSION['user_nome'] ?? 'N/A') . "</p>";
        echo "<p>Email: " . ($_SESSION['user_email'] ?? 'N/A') . "</p>";
        echo "<p>Nível: " . ($_SESSION['user_nivel'] ?? 'N/A') . "</p>";
    } else {
        echo "<p style='color:red'>✗ Nenhum usuário está logado.</p>";
    }
} else {
    echo "<p style='color:red'>✗ Sessão NÃO está ativa!</p>";
}

// Verificar permissões de diretório
echo "<h2>Verificando permissões de diretório:</h2>";
$directories = ['.', 'controllers', 'views', 'config', 'helpers', 'assets'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "<p>Diretório $dir: " . substr(sprintf('%o', fileperms($dir)), -4) . "</p>";
    } else {
        echo "<p style='color:red'>✗ Diretório $dir NÃO existe!</p>";
    }
}
