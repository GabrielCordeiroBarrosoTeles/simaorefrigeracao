<?php

namespace App\Application\Factory;

use App\Application\DTO\ClienteDTO;
use App\Domain\Entity\Cliente;
use App\Domain\Entity\ClienteTipo;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Telefone;

class ClienteFactory
{
    public function createFromDTO(ClienteDTO $dto): Cliente
    {
        $cliente = new Cliente(
            nome: $dto->nome,
            email: new Email($dto->email),
            telefone: new Telefone($dto->telefone),
            tipo: ClienteTipo::fromString($dto->tipo)
        );

        if ($dto->endereco) {
            $cliente->setEndereco($dto->endereco);
        }

        if ($dto->cidade) {
            $cliente->setCidade($dto->cidade);
        }

        if ($dto->estado) {
            $cliente->setEstado($dto->estado);
        }

        if ($dto->cep) {
            $cliente->setCep($dto->cep);
        }

        if ($dto->observacoes) {
            $cliente->setObservacoes($dto->observacoes);
        }

        return $cliente;
    }

    public function createDTOFromEntity(Cliente $cliente): ClienteDTO
    {
        return new ClienteDTO(
            id: $cliente->getId(),
            nome: $cliente->getNome(),
            email: $cliente->getEmail()->getValue(),
            telefone: $cliente->getTelefone()->getValue(),
            endereco: $cliente->getEndereco(),
            cidade: $cliente->getCidade(),
            estado: $cliente->getEstado(),
            cep: $cliente->getCep(),
            tipo: $cliente->getTipo()->value,
            observacoes: $cliente->getObservacoes(),
            dataCriacao: $cliente->getDataCriacao(),
            dataAtualizacao: $cliente->getDataAtualizacao()
        );
    }
}