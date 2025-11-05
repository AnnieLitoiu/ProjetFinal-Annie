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
        return (int) $this->createQueryBuilder('q') // 'q' est un alias pour l'entité Question
            ->select('COUNT(q.id)')
            ->andWhere('q.quiz = :idQuiz')
            ->setParameter('idQuiz', $idQuiz) // injecte la valeur dans la requête
            ->getQuery()
            ->getSingleScalarResult(); // récupère le résultat sous forme d'entier
    }

    // Renvoie une requête (Query) pour récupérer toutes les questions d'un quiz donné (par ID)
    public function requeteParQuizId(int $idQuiz): Query
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.quiz = :idQuiz')
            ->setParameter('idQuiz', $idQuiz)
            ->orderBy('q.id', 'ASC') // trie les questions par ID croissant
            ->getQuery();  // renvoie la requête (sans l'exécuter)
    }
}
