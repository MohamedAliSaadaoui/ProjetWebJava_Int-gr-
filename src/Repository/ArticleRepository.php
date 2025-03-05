<?php
namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function searchforum($searchTerm, $orderBy = 'ASC'): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.title LIKE :searchTerm OR e.content LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('e.date', $orderBy) // Tri par dateDebut avec l'ordre spécifié
            ->getQuery()
            ->getResult();
    }

    public function countByDate(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT DATE(date) AS date, COUNT(id) AS count
            FROM article
            GROUP BY DATE(date)
            ORDER BY DATE(date) ASC
        ';
        $statement = $connection->executeQuery($sql);
    
        return $statement->fetchAllAssociative();
    }
    
public function countByMonth(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT MONTH(date) as month, COUNT(id) as count
            FROM article
            GROUP BY MONTH(date)
        ';
        $statement = $connection->executeQuery($sql);

        return $statement->fetchAllAssociative();
    }

}