<?php

namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    public function findByUser($userId)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.id_reclam', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory($category)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.category = :category')
            ->setParameter('category', $category)
            ->orderBy('r.id_reclam', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus($status)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->setParameter('status', $status)
            ->orderBy('r.id_reclam', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByMaxDate()
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.dateReclamation', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function markAsResolved(int $id): void
    {
        $qb = $this->createQueryBuilder('r')
            ->update()
            ->set('r.status', ':status')
            ->where('r.id_reclam = :id')
            ->setParameter('status', 'Résolue')
            ->setParameter('id', $id);

        $qb->getQuery()->execute();
    }


    public function findById(int $id): ?Reclamation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id_reclam = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }



}
