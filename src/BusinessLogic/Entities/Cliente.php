<?php

namespace BusinessLogic\Entities;

class Cliente
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nome,
        public readonly string $email,
        public readonly string $telefone,
        public readonly string $endereco,
        public readonly string $tipo
    ) {}
    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['nome'],
            $data['email'],
            $data['telefone'],
            $data['endereco'],
            $data['tipo']
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'endereco' => $this->endereco,
            'tipo' => $this->tipo
        ];
    }
    
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->nome)) $errors[] = 'Nome é obrigatório';
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido';
        if (empty($this->telefone)) $errors[] = 'Telefone é obrigatório';
        
        return $errors;
    }
}