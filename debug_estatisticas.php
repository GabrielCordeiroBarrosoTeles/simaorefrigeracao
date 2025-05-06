<?php
require_once 'bootstrap.php';

// Verificar se o usuário está logado
if (!is_logged_in()) {
    redirect('admin-login.php');
}

// Conexão com o banco de dados
$db = db_connect();

// Função para depurar tabelas
function debug_table($db, $table_name) {
    echo "<h3>Tabela: $table_name</h3>";
    
    // Verificar se a tabela existe
    $query = "SHOW TABLES LIKE '$table_name'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "<p>A tabela $table_name não existe!</p>";
        return;
    }
    
    // Mostrar estrutura da tabela
    echo "<h4>Estrutura da tabela:</h4>";
    $query = "DESCRIBE $table_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Contar registros
    $query = "SELECT COUNT(*) as total FROM $table_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total de registros: {$count['total']}</p>";
    
    // Mostrar primeiros 5 registros
    if ($count['total'] > 0) {
        echo "<h4>Primeiros 5 registros:</h4>";
        $query = "SELECT * FROM $table_name LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1'>";
        // Cabeçalho
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>{$column['Field']}</th>";
        }
        echo "</tr>";
        
        // Dados
        foreach ($records as $record) {
            echo "<tr>";
            foreach ($columns as $column) {
                $field = $column['Field'];
                echo "<td>" . (isset($record[$field]) ? htmlspecialchars($record[$field]) : 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
}

// Depurar tabelas principais
$tables = ['clientes', 'servicos', 'tecnicos', 'agendamentos', 'contatos', 'depoimentos'];

echo "<h2>Depuração de Tabelas para Estatísticas</h2>";

foreach ($tables as $table) {
    debug_table($db, $table);
}

// Mostrar estatísticas atuais
echo "<h2>Estatísticas Atuais</h2>";

$stats = [
   'clientes' => 0,
   'agendamentos' => 0,
   'tecnicos' => 0,
   'servicos' => 0,
   'contatos' => 0,
   'depoimentos' => 0
];

try {
   // Contar clientes
   $query = "SELECT COUNT(*) as total FROM clientes";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   $stats['clientes'] = $result['total'] ?? 0;
   
   // Contar agendamentos
   $query = "SELECT COUNT(*) as total FROM agendamentos";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   $stats['agendamentos'] = $result['total'] ?? 0;
   
   // Contar técnicos
   $query = "SELECT COUNT(*) as total FROM tecnicos";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   $stats['tecnicos'] = $result['total'] ?? 0;
   
   // Contar serviços
   $query = "SELECT COUNT(*) as total FROM servicos";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   $stats['servicos'] = $result['total'] ?? 0;
   
   // Contar contatos
   $query = "SELECT COUNT(*) as total FROM contatos";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   $stats['contatos'] = $result['total'] ?? 0;
   
   // Contar depoimentos
   $query = "SELECT COUNT(*) as total FROM depoimentos";
   $stmt = $db->prepare($query);
   $stmt->execute();
   $result = $stmt->fetch(PDO::FETCH_ASSOC);
   $stats['depoimentos'] = $result['total'] ?? 0;
} catch (PDOException $e) {
   echo "<p>Erro ao buscar estatísticas: " . $e->getMessage() . "</p>";
}

echo "<table border='1'>";
echo "<tr><th>Estatística</th><th>Valor</th></tr>";
foreach ($stats as $key => $value) {
    echo "<tr><td>$key</td><td>$value</td></tr>";
}
echo "</table>";

echo "<p><a href='admin-estatisticas.php'>Voltar para Estatísticas</a></p>";
?>
