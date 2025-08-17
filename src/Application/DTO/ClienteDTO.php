<?php

namespace App\Application\DTO;

class ClienteDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $nome = '',
        public readonly string $email = '',
        public readonly string $telefone = '',
        public readonly ?string $endereco = null,
        public readonly ?string $cidade = null,
        public readonly ?string $estado = null,
        public readonly ?string $cep = null,
        public readonly string $tipo = 'residencial',
        public readonly ?string $observacoes = null,
        public readonly ?\DateTimeImmutable $dataCriacao = null,
        public readonly ?\DateTimeImmutable $dataAtualizacao = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nome: $data['nome'] ?? '',
            email: $data['email'] ?? '',
            telefone: $data['telefone'] ?? '',
            endereco: $data['endereco'] ?? null,
            cidade: $data['cidade'] ?? null,
            estado: $data['estado'] ?? null,
            cep: $data['cep'] ?? null,
            tipo: $data['tipo'] ?? 'residencial',
            observacoes: $data['observacoes'] ?? null,
            dataCriacao: isset($data['dataCriacao']) ? new \DateTimeImmutable($data['dataCriacao']) : null,
            dataAtualizacao: isset($data['dataAtualizacao']) ? new \DateTimeImmutable($data['dataAtualizacao']) : null
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
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'cep' => $this->cep,
            'tipo' => $this->tipo,
            'observacoes' => $this->observacoes,
            'dataCriacao' => $this->dataCriacao?->format('Y-m-d H:i:s'),
            'dataAtualizacao' => $this->dataAtualizacao?->format('Y-m-d H:i:s')
        ];
    }
}