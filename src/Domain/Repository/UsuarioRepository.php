<?php
namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class UsuarioRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Usuario::class);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?Usuario
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?Usuario
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findByNivel(string $nivel): array
    {
        return $this->repository->findBy(['nivel' => $nivel]);
    }

    public function save(Usuario $usuario): void
    {
        $usuario->setDataAtualizacao();
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();
    }

    public function remove(Usuario $usuario): void
    {
        $this->entityManager->remove($usuario);
        $this->entityManager->flush();
    }

    public function autenticar(string $email, string $senha): ?Usuario
    {
        $usuario = $this->findByEmail($email);
        
        if ($usuario && $usuario->verificarSenha($senha)) {
            $usuario->setUltimoLogin();
            $this->save($usuario);
            return $usuario;
        }
        
        return null;
    }

    public function findByNome(string $nome): array
    {
        $qb = $this->repository->createQueryBuilder('u');
        $qb->where('u.nome LIKE :nome')
           ->setParameter('nome', '%' . $nome . '%')
           ->orderBy('u.nome', 'ASC');

        return $qb->getQuery()->getResult();
    }
}