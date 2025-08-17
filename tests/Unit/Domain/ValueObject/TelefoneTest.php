<?php

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidTelefoneException;
use App\Domain\ValueObject\Telefone;
use PHPUnit\Framework\TestCase;

class TelefoneTest extends TestCase
{
    public function testValidTelefone(): void
    {
        $telefone = new Telefone('11987654321');
        $this->assertEquals('11987654321', $telefone->getValue());
    }

    public function testTelefoneFormatting(): void
    {
        $telefone = new Telefone('(11) 98765-4321');
        $this->assertEquals('11987654321', $telefone->getValue());
        $this->assertEquals('(11) 98765-4321', $telefone->getFormatted());
    }

    public function testInvalidTelefoneThrowsException(): void
    {
        $this->expectException(InvalidTelefoneException::class);
        new Telefone('123');
    }

    public function testTelefoneEquality(): void
    {
        $telefone1 = new Telefone('11987654321');
        $telefone2 = new Telefone('(11) 98765-4321');
        $telefone3 = new Telefone('11999999999');

        $this->assertTrue($telefone1->equals($telefone2));
        $this->assertFalse($telefone1->equals($telefone3));
    }
}