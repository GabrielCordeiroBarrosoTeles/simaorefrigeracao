<?php

namespace App\Application\Event;

interface EventInterface
{
    public function getOccurredOn(): \DateTimeImmutable;
}