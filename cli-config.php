<?php
// cli-config.php
require_once "bootstrap-doctrine.php";

// Certifique-se de que o EntityManager está disponível
$entityManager = getEntityManager();
if (!$entityManager) {
    echo "Erro: EntityManager não está disponível.\n";
    exit(1);
}

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);