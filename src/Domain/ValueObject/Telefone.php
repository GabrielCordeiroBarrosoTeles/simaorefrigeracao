<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidTelefoneException;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Telefone
{
    #[ORM\Column(type: 'string', length: 20)]
    private readonly string $value;

    public function __construct(string $telefone)
    {
        $cleaned = preg_replace('/\D/', '', $telefone);
        
        if (strlen($cleaned) < 10 || strlen($cleaned) > 11) {
            throw new InvalidTelefoneException("Telefone inválido: {$telefone}");
        }
        
        $this->value = $cleaned;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormatted(): string
    {
        if (strlen($this->value) === 11) {
            return sprintf('(%s) %s-%s', 
                substr($this->value, 0, 2),
                substr($this->value, 2, 5),
                substr($this->value, 7)
            );
        }
        
        return sprintf('(%s) %s-%s', 
            substr($this->value, 0, 2),
            substr($this->value, 2, 4),
            substr($this->value, 6)
        );
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }

    public function equals(Telefone $other): bool
    {
        return $this->value === $other->value;
    }
}