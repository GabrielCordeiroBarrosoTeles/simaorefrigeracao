<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

// Carregar o autoloader do Composer
require_once __DIR__ . '/vendor/autoload.php';

// Definir constantes de caminho se ainda não estiverem definidas
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', __DIR__);
}

// Definir constantes do banco de dados diretamente
define('DB_HOST', 'db');
define('DB_NAME', 'simaorefrigeracao');
define('DB_USER', 'simao');
define('DB_PASS', 'root');

// Configuração do Doctrine
$config = ORMSetup::createAttributeMetadataConfiguration(
    [__DIR__ . '/src/Domain/Entity'],
    true, // modo de desenvolvimento
    null,
    new PhpFilesAdapter('doctrine_metadata_cache')
);

// Configuração da conexão com o banco de dados
$connectionParams = [
    'driver'   => 'pdo_mysql',
    'host'     => DB_HOST,
    'dbname'   => DB_NAME,
    'user'     => DB_USER,
    'password' => DB_PASS,
    'charset'  => 'utf8mb4'
];

// Criar o EntityManager
try {
    $entityManager = EntityManager::create($connectionParams, $config);
    
    // Configuração das Migrations
    $migrationConfig = new PhpFile(__DIR__ . '/migrations.php');
    $dependencyFactory = DependencyFactory::fromEntityManager($migrationConfig, new ExistingEntityManager($entityManager));
} catch (Exception $e) {
    echo "Erro ao criar EntityManager: " . $e->getMessage() . "\n";
    $entityManager = null;
    $dependencyFactory = null;
}

// Função para obter o EntityManager
function getEntityManager() {
    global $entityManager;
    return $entityManager;
}

// Função para obter o DependencyFactory das Migrations
function getMigrationDependencyFactory() {
    global $dependencyFactory;
    return $dependencyFactory;
}