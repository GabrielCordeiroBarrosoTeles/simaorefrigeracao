<?php

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Cliente;
use App\Domain\Entity\ClienteTipo;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Telefone;
use PHPUnit\Framework\TestCase;

class ClienteTest extends TestCase
{
    public function testClienteCreation(): void
    {
        $cliente = new Cliente(
            'João Silva',
            new Email('joao@email.com'),
            new Telefone('11987654321'),
            ClienteTipo::RESIDENCIAL
        );

        $this->assertEquals('João Silva', $cliente->getNome());
        $this->assertEquals('joao@email.com', $cliente->getEmail()->getValue());
        $this->assertEquals('11987654321', $cliente->getTelefone()->getValue());
        $this->assertEquals(ClienteTipo::RESIDENCIAL, $cliente->getTipo());
        $this->assertInstanceOf(\DateTimeImmutable::class, $cliente->getDataCriacao());
    }

    public function testClienteUpdate(): void
    {
        $cliente = new Cliente(
            'João Silva',
            new Email('joao@email.com'),
            new Telefone('11987654321')
        );

        $cliente->setEndereco('Rua das Flores, 123')
               ->setCidade('São Paulo')
               ->setEstado('SP')
               ->setCep('01234-567');

        $this->assertEquals('Rua das Flores, 123', $cliente->getEndereco());
        $this->assertEquals('São Paulo', $cliente->getCidade());
        $this->assertEquals('SP', $cliente->getEstado());
        $this->assertEquals('01234-567', $cliente->getCep());
    }
}