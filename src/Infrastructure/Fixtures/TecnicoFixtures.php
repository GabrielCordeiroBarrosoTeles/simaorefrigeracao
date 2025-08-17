<?php

namespace App\Infrastructure\Fixtures;

use App\Domain\Entity\Tecnico;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Telefone;
use Doctrine\ORM\EntityManagerInterface;

class TecnicoFixtures
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function load(): void
    {
        $tecnicos = [
            ['Carlos Silva', 'carlos.silva@simao.com', '11987654321', 'Instalação', '#3b82f6'],
            ['Marcos Oliveira', 'marcos.oliveira@simao.com', '11976543210', 'Manutenção', '#10b981'],
            ['Pedro Santos', 'pedro.santos@simao.com', '11965432109', 'Câmaras Frigoríficas', '#f59e0b'],
            ['Ana Costa', 'ana.costa@simao.com', '11954321098', 'Projetos', '#8b5cf6'],
        ];

        foreach ($tecnicos as [$nome, $email, $telefone, $especialidade, $cor]) {
            $tecnico = new Tecnico(
                nome: $nome,
                email: new Email($email),
                telefone: new Telefone($telefone)
            );
            
            $tecnico->setEspecialidade($especialidade)->setCor($cor);
            
            $this->entityManager->persist($tecnico);
        }

        $this->entityManager->flush();
    }
}