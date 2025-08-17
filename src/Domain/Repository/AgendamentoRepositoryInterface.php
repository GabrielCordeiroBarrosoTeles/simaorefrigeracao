<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Agendamento;
use App\Domain\Entity\Cliente;
use App\Domain\Entity\Tecnico;

interface AgendamentoRepositoryInterface
{
    public function save(Agendamento $agendamento): void;
    public function findById(int $id): ?Agendamento;
    public function findAll(): array;
    public function delete(Agendamento $agendamento): void;
    public function findByCliente(Cliente $cliente): array;
    public function findByTecnico(Tecnico $tecnico): array;
    public function findByDateRange(\DateTime $inicio, \DateTime $fim): array;
    public function findByStatus(string $status): array;
}