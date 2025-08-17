<?php

namespace App\Infrastructure\Fixtures;

use App\Domain\Entity\Servico;
use Doctrine\ORM\EntityManagerInterface;

class ServicoFixtures
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function load(): void
    {
        $servicos = [
            [
                'Instalação de Ar Condicionado',
                'fan',
                'Instalação profissional de equipamentos residenciais e comerciais.',
                ['Instalação de splits e multi-splits', 'Instalação de ar condicionado central', 'Instalação de VRF/VRV'],
                12
            ],
            [
                'Manutenção Preventiva',
                'thermometer',
                'Serviços regulares para garantir o funcionamento ideal do seu equipamento.',
                ['Limpeza de filtros e componentes', 'Verificação de gás refrigerante', 'Inspeção de componentes elétricos'],
                6
            ],
            [
                'Manutenção Corretiva',
                'tools',
                'Reparo rápido e eficiente para resolver problemas no seu equipamento.',
                ['Diagnóstico preciso de falhas', 'Reparo de vazamentos', 'Substituição de componentes'],
                3
            ],
            [
                'Câmara Frigorífica',
                'snowflake',
                'Soluções para armazenamento refrigerado comercial e industrial.',
                ['Instalação de câmaras frigoríficas', 'Manutenção de sistemas de refrigeração', 'Projetos personalizados'],
                24
            ]
        ];

        foreach ($servicos as [$titulo, $icone, $descricao, $itens, $garantia]) {
            $servico = new Servico($titulo, $descricao, $itens);
            $servico->setIcone($icone)->setGarantiaMeses($garantia);
            
            $this->entityManager->persist($servico);
        }

        $this->entityManager->flush();
    }
}