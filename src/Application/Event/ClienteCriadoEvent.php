<?php

namespace App\Application\Event;

use App\Domain\Entity\Cliente;

class ClienteCriadoEvent implements EventInterface
{
    private \DateTimeImmutable $occurredOn;

    public function __construct(private Cliente $cliente)
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}