<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Cliente;
use App\Domain\Repository\ClienteRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class DoctrineClienteRepository implements ClienteRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Cliente::class);
    }

    public function save(Cliente $cliente): void
    {
        $this->entityManager->persist($cliente);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?Cliente
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?Cliente
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findAll(): array
    {
        return $this->repository->findBy([], ['nome' => 'ASC']);
    }

    public function delete(Cliente $cliente): void
    {
        $this->entityManager->remove($cliente);
        $this->entityManager->flush();
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->repository->createQueryBuilder('c');

        if (!empty($filters['nome'])) {
            $qb->andWhere('c.nome LIKE :nome')
               ->setParameter('nome', '%' . $filters['nome'] . '%');
        }

        if (!empty($filters['tipo'])) {
            $qb->andWhere('c.tipo = :tipo')
               ->setParameter('tipo', $filters['tipo']);
        }

        if (!empty($filters['cidade'])) {
            $qb->andWhere('c.cidade LIKE :cidade')
               ->setParameter('cidade', '%' . $filters['cidade'] . '%');
        }

        return $qb->getQuery()->getResult();
    }
}