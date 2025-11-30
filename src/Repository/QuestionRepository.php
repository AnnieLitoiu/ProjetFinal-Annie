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

    /**
     * Tire aléatoirement N IDs de questions pour un quiz donné.
     * Utilise RAND() (MySQL). Si vous changez de SGBD, adaptez l'expression.
     * @return int[]
     */
    public function tirerAleatoireIdsParQuiz(int $idQuiz, int $limite): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT id FROM question WHERE quiz_id = :idQuiz ORDER BY RAND() LIMIT :lim';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('idQuiz', $idQuiz);
        // LIMIT doit être bindé en entier
        $stmt->bindValue('lim', $limite, \PDO::PARAM_INT);
        $rows = $stmt->executeQuery()->fetchFirstColumn();
        return array_map('intval', $rows);
    }

    /**
     * Retourne les questions correspondant à une liste d'IDs, dans le même ordre que la liste fournie.
     * Retourne un tableau de Question (pas une Query) pour éviter l'usage de fonctions SQL spécifiques.
     * @return Question[]
     */
    public function requeteParIdsAvecOrdre(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $questions = $this->createQueryBuilder('q')
            ->andWhere('q.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $parId = [];
        foreach ($questions as $q) {
            $parId[$q->getId()] = $q;
        }
        $ordonnee = [];
        foreach ($ids as $id) {
            if (isset($parId[$id])) {
                $ordonnee[] = $parId[$id];
            }
        }
        return $ordonnee;
    }
}
