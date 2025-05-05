<?php
// Iniciar sessão
session_start();

echo "<h1>Correção de Configuração do Banco de Dados</h1>";

// 1. Verificar e corrigir o arquivo config/database.php
echo "<h2>1. Verificando arquivo de configuração do banco de dados</h2>";

$database_file = 'config/database.php';
if (file_exists($database_file)) {
    $content = file_get_contents($database_file);
    
    // Verificar se o nome do banco de dados está correto
    if (strpos($content, "define('DB_NAME', 'friocerto')") !== false) {
        // Substituir o nome do banco de dados
        $content = str_replace(
            "define('DB_NAME', 'friocerto')", 
            "define('DB_NAME', 'simaorefrigeracao')", 
            $content
        );
        
        // Salvar o arquivo atualizado
        file_put_contents($database_file, $content);
        echo "<div class='alert alert-success'>Nome do banco de dados corrigido no arquivo $database_file</div>";
    } else if (strpos($content, "define('DB_NAME', 'simaorefrigeracao')") !== false) {
        echo "<div class='alert alert-info'>Nome do banco de dados já está correto no arquivo $database_file</div>";
    } else {
        echo "<div class='alert alert-warning'>Não foi possível encontrar a definição do nome do banco de dados no arquivo $database_file</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Arquivo $database_file não encontrado</div>";
}

// 2. Verificar e corrigir outros arquivos que possam conter o nome do banco de dados
echo "<h2>2. Verificando outros arquivos de configuração</h2>";

// Verificar o arquivo Database.php se existir
$database_class_file = 'config/Database.php';
if (file_exists($database_class_file)) {
    $content = file_get_contents($database_class_file);
    
    // Verificar se o nome do banco de dados está correto
    if (strpos($content, '$db_name = "friocerto"') !== false) {
        // Substituir o nome do banco de dados
        $content = str_replace(
            '$db_name = "friocerto"', 
            '$db_name = "simaorefrigeracao"', 
            $content
        );
        
        // Salvar o arquivo atualizado
        file_put_contents($database_class_file, $content);
        echo "<div class='alert alert-success'>Nome do banco de dados corrigido no arquivo $database_class_file</div>";
    } else {
        echo "<div class='alert alert-info'>Não foi necessário corrigir o arquivo $database_class_file</div>";
    }
}

// 3. Verificar a conexão com o banco de dados
echo "<h2>3. Verificando conexão com o banco de dados</h2>";

// Incluir o arquivo de configuração
require_once 'config/database.php';

try {
    // Tentar conectar ao banco de dados
    $db = db_connect();
    echo "<div class='alert alert-success'>Conexão com o banco de dados estabelecida com sucesso!</div>";
    
    // Verificar quais tabelas existem
    echo "<h3>Tabelas encontradas no banco de dados:</h3>";
    $query = "SHOW TABLES";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "<div class='alert alert-warning'>Nenhuma tabela encontrada no banco de dados.</div>";
    }
    
    // Verificar se as tabelas necessárias existem
    $required_tables = [
        'agendamentos', 'clientes', 'configuracoes', 'contatos', 
        'depoimentos', 'estatisticas', 'servicos', 'tecnicos', 'usuarios'
    ];
    
    $missing_tables = array_diff($required_tables, $tables);
    
    if (count($missing_tables) > 0) {
        echo "<div class='alert alert-warning'>As seguintes tabelas estão faltando: " . implode(', ', $missing_tables) . "</div>";
    } else {
        echo "<div class='alert alert-success'>Todas as tabelas necessárias estão presentes!</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao conectar ao banco de dados: " . $e->getMessage() . "</div>";
}

// 4. Verificar e corrigir a função get_flash_message
echo "<h2>4. Verificando função get_flash_message</h2>";

$functions_file = 'helpers/functions.php';
if (file_exists($functions_file)) {
    $content = file_get_contents($functions_file);
    
    // Verificar se a função get_flash_message existe
    if (strpos($content, 'function get_flash_message') === false) {
        // Adicionar a função get_flash_message
        $function_code = '
// Obter mensagem flash
function get_flash_message() {
    if (isset($_SESSION["flash_message"])) {
        $message = $_SESSION["flash_message"];
        unset($_SESSION["flash_message"]);
        return $message;
    }
    return null;
}
';
        
        // Encontrar a posição após a função set_flash_message
        $pos = strpos($content, 'function set_flash_message');
        if ($pos !== false) {
            $end_pos = strpos($content, '}', $pos);
            if ($end_pos !== false) {
                $new_content = substr($content, 0, $end_pos + 1) . $function_code . substr($content, $end_pos + 1);
                file_put_contents($functions_file, $new_content);
                echo "<div class='alert alert-success'>Função get_flash_message adicionada ao arquivo $functions_file</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Não foi possível encontrar a posição para adicionar a função get_flash_message</div>";
        }
    } else {
        echo "<div class='alert alert-info'>Função get_flash_message já existe no arquivo $functions_file</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Arquivo $functions_file não encontrado</div>";
}

echo "<h2>Correções concluídas!</h2>";
echo "<p>Agora você pode:</p>";
echo "<ul>";
echo "<li><a href='admin-login.php'>Ir para a página de login</a></li>";
echo "<li><a href='admin-dashboard.php'>Ir para o painel administrativo</a></li>";
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
    h1, h2, h3 {
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
