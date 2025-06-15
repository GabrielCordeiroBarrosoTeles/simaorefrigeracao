<?php
namespace App\Repository;

use App\Entity\Contato;
use App\Entity\Servico;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ContatoRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Contato::class);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?Contato
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): array
    {
        return $this->repository->findBy(['email' => $email]);
    }

    public function findByServico(Servico $servico): array
    {
        return $this->repository->findBy(['servico' => $servico]);
    }

    public function findByStatus(string $status): array
    {
        return $this->repository->findBy(['status' => $status]);
    }

    public function save(Contato $contato): void
    {
        $contato->setDataAtualizacao();
        $this->entityManager->persist($contato);
        $this->entityManager->flush();
    }

    public function remove(Contato $contato): void
    {
        $this->entityManager->remove($contato);
        $this->entityManager->flush();
    }

    public function findByNome(string $nome): array
    {
        $qb = $this->repository->createQueryBuilder('c');
        $qb->where('c.nome LIKE :nome')
           ->setParameter('nome', '%' . $nome . '%')
           ->orderBy('c.data_criacao', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function countByStatus(string $status): int
    {
        $qb = $this->repository->createQueryBuilder('c');
        $qb->select('COUNT(c.id)')
           ->where('c.status = :status')
           ->setParameter('status', $status);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}