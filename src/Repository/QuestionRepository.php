<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function compterParQuizId(int $idQuiz): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->andWhere('q.quiz = :idQuiz')
            ->setParameter('idQuiz', $idQuiz)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function requeteParQuizId(int $idQuiz): Query
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.quiz = :idQuiz')
            ->setParameter('idQuiz', $idQuiz)
            ->orderBy('q.id', 'ASC') 
            ->getQuery();
    }
}
