<?php
namespace App\Repository;

use App\Entity\Cliente;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClienteRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Cliente::class);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?Cliente
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?Cliente
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findByTipo(string $tipo): array
    {
        return $this->repository->findBy(['tipo' => $tipo]);
    }

    public function save(Cliente $cliente): void
    {
        $cliente->setDataAtualizacao();
        $this->entityManager->persist($cliente);
        $this->entityManager->flush();
    }

    public function remove(Cliente $cliente): void
    {
        $this->entityManager->remove($cliente);
        $this->entityManager->flush();
    }

    public function countByTipo(string $tipo): int
    {
        $qb = $this->repository->createQueryBuilder('c');
        $qb->select('COUNT(c.id)')
           ->where('c.tipo = :tipo')
           ->setParameter('tipo', $tipo);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findByNome(string $nome): array
    {
        $qb = $this->repository->createQueryBuilder('c');
        $qb->where('c.nome LIKE :nome')
           ->setParameter('nome', '%' . $nome . '%')
           ->orderBy('c.nome', 'ASC');

        return $qb->getQuery()->getResult();
    }
}