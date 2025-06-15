<?php
namespace App\Repository;

use App\Entity\Tecnico;
use App\Entity\Usuario;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class TecnicoRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Tecnico::class);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?Tecnico
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?Tecnico
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findByUsuario(Usuario $usuario): ?Tecnico
    {
        return $this->repository->findOneBy(['usuario' => $usuario]);
    }

    public function findByStatus(string $status): array
    {
        return $this->repository->findBy(['status' => $status]);
    }

    public function save(Tecnico $tecnico): void
    {
        $tecnico->setDataAtualizacao();
        $this->entityManager->persist($tecnico);
        $this->entityManager->flush();
    }

    public function remove(Tecnico $tecnico): void
    {
        $this->entityManager->remove($tecnico);
        $this->entityManager->flush();
    }

    public function findAllAtivos(): array
    {
        return $this->repository->findBy(['status' => 'ativo']);
    }

    public function findByNome(string $nome): array
    {
        $qb = $this->repository->createQueryBuilder('t');
        $qb->where('t.nome LIKE :nome')
           ->setParameter('nome', '%' . $nome . '%')
           ->orderBy('t.nome', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByEspecialidade(string $especialidade): array
    {
        $qb = $this->repository->createQueryBuilder('t');
        $qb->where('t.especialidade LIKE :especialidade')
           ->setParameter('especialidade', '%' . $especialidade . '%')
           ->orderBy('t.nome', 'ASC');

        return $qb->getQuery()->getResult();
    }
}