<?php
// Arquivo para configurar o banco de dados
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir arquivo de configuração do banco de dados
require_once 'config/database.php';

// Tentar estabelecer conexão
try {
    $db = db_connect();
    echo "<h2>Conexão com o banco de dados estabelecida com sucesso!</h2>";
    
    // Ler o arquivo SQL
    $sql = file_get_contents('verificar_criar_tabelas.sql');
    
    // Executar as consultas SQL
    echo "<h3>Executando consultas SQL:</h3>";
    
    // Dividir o arquivo SQL em consultas individuais
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        
        if (!empty($query)) {
            try {
                $db->exec($query);
                echo "<p style='color:green'>Consulta executada com sucesso.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erro ao executar consulta: " . $e->getMessage() . "</p>";
                echo "<p>Query: " . htmlspecialchars($query) . "</p>";
            }
        }
    }
    
    echo "<h3>Configuração do banco de dados concluída!</h3>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Erro na conexão com o banco de dados:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<p><a href="debug_db.php">Verificar conexão com o banco de dados</a></p>
<p><a href="index.php">Voltar para a página inicial</a></p>
