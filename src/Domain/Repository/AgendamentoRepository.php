<?php
namespace App\Repository;

use App\Entity\Agendamento;
use App\Entity\Cliente;
use App\Entity\Tecnico;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class AgendamentoRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Agendamento::class);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?Agendamento
    {
        return $this->repository->find($id);
    }

    public function findByCliente(Cliente $cliente): array
    {
        return $this->repository->findBy(['cliente' => $cliente]);
    }

    public function findByTecnico(Tecnico $tecnico): array
    {
        return $this->repository->findBy(['tecnico' => $tecnico]);
    }

    public function findByData(\DateTime $data): array
    {
        return $this->repository->findBy(['data_agendamento' => $data]);
    }

    public function findByStatus(string $status): array
    {
        return $this->repository->findBy(['status' => $status]);
    }

    public function save(Agendamento $agendamento): void
    {
        $agendamento->setDataAtualizacao();
        $this->entityManager->persist($agendamento);
        $this->entityManager->flush();
    }

    public function remove(Agendamento $agendamento): void
    {
        $this->entityManager->remove($agendamento);
        $this->entityManager->flush();
    }

    public function findByPeriodo(\DateTime $inicio, \DateTime $fim): array
    {
        $qb = $this->repository->createQueryBuilder('a');
        $qb->where('a.data_agendamento >= :inicio')
           ->andWhere('a.data_agendamento <= :fim')
           ->setParameter('inicio', $inicio)
           ->setParameter('fim', $fim)
           ->orderBy('a.data_agendamento', 'ASC')
           ->addOrderBy('a.hora_inicio', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByTecnicoEData(Tecnico $tecnico, \DateTime $data): array
    {
        $qb = $this->repository->createQueryBuilder('a');
        $qb->where('a.tecnico = :tecnico')
           ->andWhere('a.data_agendamento = :data')
           ->setParameter('tecnico', $tecnico)
           ->setParameter('data', $data)
           ->orderBy('a.hora_inicio', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function countByStatus(string $status): int
    {
        $qb = $this->repository->createQueryBuilder('a');
        $qb->select('COUNT(a.id)')
           ->where('a.status = :status')
           ->setParameter('status', $status);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}