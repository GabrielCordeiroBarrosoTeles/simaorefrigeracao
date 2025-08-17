<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Cliente;

interface ClienteRepositoryInterface
{
    public function save(Cliente $cliente): void;
    public function findById(int $id): ?Cliente;
    public function findByEmail(string $email): ?Cliente;
    public function findAll(): array;
    public function delete(Cliente $cliente): void;
    public function findByFilters(array $filters): array;
}