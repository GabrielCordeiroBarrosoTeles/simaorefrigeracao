<?php

namespace App\Infrastructure\Fixtures;

use App\Domain\Entity\Cliente;
use App\Domain\Entity\ClienteTipo;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Telefone;
use Doctrine\ORM\EntityManagerInterface;

class ClienteFixtures
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function load(): void
    {
        $clientes = [
            ['João Silva', 'joao.silva@email.com', '11987654321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', ClienteTipo::RESIDENCIAL],
            ['Maria Santos', 'maria.santos@email.com', '11976543210', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', ClienteTipo::RESIDENCIAL],
            ['Empresa ABC Ltda', 'contato@empresaabc.com', '1133334444', 'Rua Comercial, 500', 'São Paulo', 'SP', '01234-000', ClienteTipo::COMERCIAL],
            ['Indústria XYZ', 'contato@industriaxyz.com', '1133335555', 'Av. Industrial, 2000', 'Guarulhos', 'SP', '07000-000', ClienteTipo::INDUSTRIAL],
            ['Pedro Oliveira', 'pedro.oliveira@email.com', '11965432109', 'Rua das Palmeiras, 456', 'São Paulo', 'SP', '04567-890', ClienteTipo::RESIDENCIAL],
        ];

        foreach ($clientes as [$nome, $email, $telefone, $endereco, $cidade, $estado, $cep, $tipo]) {
            $cliente = new Cliente(
                nome: $nome,
                email: new Email($email),
                telefone: new Telefone($telefone),
                tipo: $tipo
            );
            
            $cliente->setEndereco($endereco)
                   ->setCidade($cidade)
                   ->setEstado($estado)
                   ->setCep($cep);

            $this->entityManager->persist($cliente);
        }

        $this->entityManager->flush();
    }
}