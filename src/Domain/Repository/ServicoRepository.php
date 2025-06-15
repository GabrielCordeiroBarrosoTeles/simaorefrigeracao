<?php
namespace App\Repository;

use App\Entity\Servico;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class ServicoRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Servico::class);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?Servico
    {
        return $this->repository->find($id);
    }

    public function findByTitulo(string $titulo): ?Servico
    {
        return $this->repository->findOneBy(['titulo' => $titulo]);
    }

    public function save(Servico $servico): void
    {
        $servico->setDataAtualizacao();
        $this->entityManager->persist($servico);
        $this->entityManager->flush();
    }

    public function remove(Servico $servico): void
    {
        $this->entityManager->remove($servico);
        $this->entityManager->flush();
    }

    public function findByTituloLike(string $titulo): array
    {
        $qb = $this->repository->createQueryBuilder('s');
        $qb->where('s.titulo LIKE :titulo')
           ->setParameter('titulo', '%' . $titulo . '%')
           ->orderBy('s.titulo', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findAllOrdered(): array
    {
        $qb = $this->repository->createQueryBuilder('s');
        $qb->orderBy('s.titulo', 'ASC');

        return $qb->getQuery()->getResult();
    }
}