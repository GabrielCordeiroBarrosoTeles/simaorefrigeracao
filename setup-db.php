<?php
// Configuração direta para o banco de dados
$host = 'db';
$dbname = 'simaorefrigeracao';
$user = 'simao';
$password = 'root';

try {
    echo "Tentando conectar ao MySQL...\n";
    
    // Conectar ao MySQL sem selecionar um banco de dados
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexão estabelecida com sucesso!\n";
    
    // Criar o banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco de dados '$dbname' criado ou já existente.\n";
    
    // Selecionar o banco de dados
    $pdo->exec("USE `$dbname`");
    
    // Criar tabelas básicas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
            `version` varchar(191) NOT NULL,
            `executed_at` datetime DEFAULT NULL,
            `execution_time` int(11) DEFAULT NULL,
            PRIMARY KEY (`version`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "Tabela de migrations criada.\n";
    
    // Importar o arquivo SQL principal se existir
    if (file_exists(__DIR__ . '/simaorefrigeracao.sql')) {
        echo "Importando arquivo SQL...\n";
        $sql = file_get_contents(__DIR__ . '/simaorefrigeracao.sql');
        
        // Dividir o SQL em comandos individuais
        $commands = explode(';', $sql);
        foreach ($commands as $command) {
            $command = trim($command);
            if (!empty($command)) {
                try {
                    $pdo->exec($command);
                } catch (PDOException $e) {
                    echo "Erro ao executar comando SQL: " . $e->getMessage() . "\n";
                    echo "Comando: " . substr($command, 0, 100) . "...\n";
                    // Continuar com o próximo comando
                }
            }
        }
        echo "Arquivo SQL importado com sucesso.\n";
    } else {
        echo "Arquivo SQL não encontrado.\n";
    }
    
    echo "Configuração do banco de dados concluída com sucesso!\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}