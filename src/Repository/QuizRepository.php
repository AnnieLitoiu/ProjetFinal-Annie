<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    public function requeteParNiveauId(int $idNiveau): Query
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.niveau = :niveau')
            ->setParameter('niveau', $idNiveau)
            ->orderBy('q.id', 'ASC') 
            ->getQuery();
    }

    public function compterParNiveauId(int $idNiveau): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->andWhere('q.niveau = :niveau')
            ->setParameter('niveau', $idNiveau)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
