<?php

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240517000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adiciona índices para melhorar performance';
    }

    public function up(Schema $schema): void
    {
        // Adicionar índices para melhorar performance de consultas frequentes
        $this->addSql('CREATE INDEX idx_agendamentos_data_hora ON agendamentos (data_agendamento, hora_inicio)');
        $this->addSql('CREATE INDEX idx_clientes_nome ON clientes (nome)');
        $this->addSql('CREATE INDEX idx_tecnicos_especialidade ON tecnicos (especialidade)');
    }

    public function down(Schema $schema): void
    {
        // Remover índices adicionados
        $this->addSql('DROP INDEX idx_agendamentos_data_hora ON agendamentos');
        $this->addSql('DROP INDEX idx_clientes_nome ON clientes');
        $this->addSql('DROP INDEX idx_tecnicos_especialidade ON tecnicos');
    }
}