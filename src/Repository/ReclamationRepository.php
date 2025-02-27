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

// Catégorie la plus signalée
    public function getMostReportedCategory(): ?string
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.category, COUNT(r.id_reclam) as count')
            ->groupBy('r.category')
            ->orderBy('count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['category'] : null;
    }


// Nombre de réclamations résolues
    public function countResolved(): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id_reclam)')
            ->where('r.status = :status')
            ->setParameter('status', 'résolue')
            ->getQuery()
            ->getSingleScalarResult();
    }

// Nombre de réclamations en cours
    public function countInProgress(): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id_reclam)')
            ->where('r.status = :status')
            ->setParameter('status', 'En Cours')
            ->getQuery()
            ->getSingleScalarResult();
    }

// Utilisateur ayant fait le plus de réclamations
    public function getTopUser(): ?string
    {
        $result = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->select('u.name, COUNT(r.id_reclam) as count')
            ->groupBy('u.name')
            ->orderBy('count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['name'] : null;
    }


// Date avec le peak de réclamations
    public function getPeakComplaintDate(): ?\DateTime
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.dateReclamation as date, COUNT(r.id_reclam) as count')
            ->groupBy('date')
            ->orderBy('count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['date'] : null;
    }

    public function findFilteredReclamations(?string $search, ?string $sort, array $statuses): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u') // Joindre l'utilisateur
            ->addSelect('u');

        // 🔎 Filtrer par username
        if ($search) {
            $qb->andWhere('u.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // ✅ Filtrer par statut (cases cochées)
        if (!empty($statuses)) {
            $qb->andWhere('r.status IN (:statuses)')
                ->setParameter('statuses', $statuses);
        }

        // 🔃 Appliquer le tri
        switch ($sort) {
            case 'date':
                $qb->orderBy('r.dateReclamation', 'DESC');
                break;
            case 'status':
                $qb->orderBy('r.status', 'ASC');
                break;
            case 'username':
                $qb->orderBy('u.name', 'ASC');
                break;
            default:
                $qb->orderBy('r.dateReclamation', 'DESC'); // Trier par date par défaut
        }

        return $qb->getQuery()->getResult();
    }



}
