<?php

namespace App\Infrastructure\Command;

use App\Infrastructure\Fixtures\ClienteFixtures;
use App\Infrastructure\Fixtures\ServicoFixtures;
use App\Infrastructure\Fixtures\TecnicoFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends Command
{
    protected static $defaultName = 'fixtures:load';

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Carrega dados fictícios no banco de dados');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Carregando fixtures...');

        $clienteFixtures = new ClienteFixtures($this->entityManager);
        $clienteFixtures->load();
        $output->writeln('✓ Clientes carregados');

        $servicoFixtures = new ServicoFixtures($this->entityManager);
        $servicoFixtures->load();
        $output->writeln('✓ Serviços carregados');

        $tecnicoFixtures = new TecnicoFixtures($this->entityManager);
        $tecnicoFixtures->load();
        $output->writeln('✓ Técnicos carregados');

        $output->writeln('Fixtures carregadas com sucesso!');
        return Command::SUCCESS;
    }
}