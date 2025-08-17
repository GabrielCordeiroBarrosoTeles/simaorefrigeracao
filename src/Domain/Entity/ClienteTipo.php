<?php

namespace App\Domain\Entity;

enum ClienteTipo: string
{
    case RESIDENCIAL = 'residencial';
    case COMERCIAL = 'comercial';
    case INDUSTRIAL = 'industrial';

    public function getLabel(): string
    {
        return match($this) {
            self::RESIDENCIAL => 'Residencial',
            self::COMERCIAL => 'Comercial',
            self::INDUSTRIAL => 'Industrial',
        };
    }

    public static function fromString(string $value): self
    {
        return match(strtolower($value)) {
            'residencial' => self::RESIDENCIAL,
            'comercial' => self::COMERCIAL,
            'industrial' => self::INDUSTRIAL,
            default => throw new \InvalidArgumentException("Tipo de cliente inválido: {$value}")
        };
    }
}