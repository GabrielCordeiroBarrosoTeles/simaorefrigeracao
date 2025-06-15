<?php

// Definir constantes do banco de dados diretamente
define('DB_HOST', 'db');
define('DB_NAME', 'simaorefrigeracao');
define('DB_USER', 'simao');
define('DB_PASS', 'root');

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
        'version_column_name' => 'version',
        'version_column_length' => 191,
        'executed_at_column_name' => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],

    'migrations_paths' => [
        'App\Infrastructure\Persistence\Migrations' => __DIR__ . '/src/Infrastructure/Persistence/Migrations',
    ],

    'all_or_nothing' => true,
    'check_database_platform' => true,
    'organize_migrations' => 'none',
    'connection' => [
        'driver' => 'pdo_mysql',
        'host' => DB_HOST,
        'dbname' => DB_NAME,
        'user' => DB_USER,
        'password' => DB_PASS,
        'charset' => 'utf8mb4'
    ],
];