<?php
// Arquivo para corrigir o banco de dados
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Verificação e Correção do Banco de Dados</h1>";

// Verificar qual banco de dados está configurado
require_once 'config/database.php';

echo "<h2>Configuração atual do banco de dados:</h2>";
echo "<p>Host: " . DB_HOST . "</p>";
echo "<p>Nome do banco: " . DB_NAME . "</p>";
echo "<p>Usuário: " . DB_USER . "</p>";

// Tentar estabelecer conexão
try {
    $db = db_connect();
    echo "<p style='color:green'>Conexão com o banco de dados estabelecida com sucesso!</p>";
    
    // Criar as tabelas faltantes
    echo "<h2>Criando tabelas faltantes:</h2>";
    
    // Tabela clientes
    try {
        $sql = "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            telefone VARCHAR(20),
            endereco TEXT,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->exec($sql);
        echo "<p style='color:green'>Tabela 'clientes' criada ou já existente.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro ao criar tabela 'clientes': " . $e->getMessage() . "</p>";
    }
    
    // Tabela tecnicos
    try {
        $sql = "CREATE TABLE IF NOT EXISTS tecnicos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            telefone VARCHAR(20),
            especialidade VARCHAR(100),
            usuario_id INT,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->exec($sql);
        echo "<p style='color:green'>Tabela 'tecnicos' criada ou já existente.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro ao criar tabela 'tecnicos': " . $e->getMessage() . "</p>";
    }
    
    // Tabela servicos (caso não exista)
    try {
        $sql = "CREATE TABLE IF NOT EXISTS servicos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(100) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10,2),
            imagem VARCHAR(255),
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->exec($sql);
        echo "<p style='color:green'>Tabela 'servicos' criada ou já existente.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro ao criar tabela 'servicos': " . $e->getMessage() . "</p>";
    }
    
    // Tabela agendamentos
    try {
        $sql = "CREATE TABLE IF NOT EXISTS agendamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT,
            tecnico_id INT,
            servico_id INT,
            data_agendamento DATETIME NOT NULL,
            status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') DEFAULT 'pendente',
            observacoes TEXT,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
            FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL,
            FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->exec($sql);
        echo "<p style='color:green'>Tabela 'agendamentos' criada ou já existente.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro ao criar tabela 'agendamentos': " . $e->getMessage() . "</p>";
    }
    
    // Verificar tabelas novamente
    echo "<h2>Verificando tabelas após a criação:</h2>";
    echo "<ul>";
    
    $tables = ['clientes', 'tecnicos', 'servicos', 'agendamentos', 'usuarios'];
    
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

<p><a href="debug_db.php">Verificar conexão com o banco de dados</a></p>
<p><a href="index.php">Voltar para a página inicial</a></p>
