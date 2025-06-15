<?php

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240517000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migração inicial do banco de dados';
    }

    public function up(Schema $schema): void
    {
        // Esta migração assume que o banco de dados já existe
        // Apenas para verificar a conexão e registrar a migração
        $this->addSql('SELECT 1');
    }

    public function down(Schema $schema): void
    {
        // Esta migração não faz nada no down
    }
}