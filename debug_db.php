<?php
// Arquivo para depurar a conexão com o banco de dados
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir arquivo de configuração do banco de dados
require_once 'config/database.php';

// Tentar estabelecer conexão
try {
    $db = db_connect();
    echo "<h2>Conexão com o banco de dados estabelecida com sucesso!</h2>";
    
    // Verificar tabelas
    $tables = ['clientes', 'tecnicos', 'agendamentos', 'usuarios'];
    
    echo "<h3>Verificando tabelas:</h3>";
    echo "<ul>";
    
    foreach ($tables as $table) {
        try {
            $query = "SELECT 1 FROM $table LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute();
            echo "<li>Tabela <strong>$table</strong>: <span style='color:green'>OK</span></li>";
        } catch (PDOException $e) {
            echo "<li>Tabela <strong>$table</strong>: <span style='color:red'>ERRO - " . $e->getMessage() . "</span></li>";
        }
    }
    
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Erro na conexão com o banco de dados:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<p><a href="index.php">Voltar para a página inicial</a></p>
