<?php

namespace App\Repository;

use App\Entity\Quiz;
use App\Entity\Tentative;
use App\Entity\UserBadge;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Utilisateur;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Tentative>
 */
class TentativeRepository extends ServiceEntityRepository
{
    public const MAX_TENTATIVES = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tentative::class);
    }

    public function saveTentative(
        Quiz $quiz,
        Utilisateur $utilisateur,
        int $nombreQuestions,
        int $tempsAlloueSecondes
    ): Tentative {
        $tentative = new Tentative();
        $tentative->setDateDebut(new DateTime('now'));
        $tentative->setMaxTentatives(self::MAX_TENTATIVES);
        $tentative->setPourcentage(0);
        $tentative->setQuiz($quiz);
        $tentative->setUtilisateur($utilisateur);
        if ($nombreQuestions !== null) {
            $tentative->setNombreQuestions($nombreQuestions);
        }
        if ($tempsAlloueSecondes !== null) {
            $tentative->setTempsAlloueSecondes($tempsAlloueSecondes);
        }
        $em = $this->getEntityManager();
        $em->persist($tentative);
        $em->flush();

        return $tentative;
    }

    public function finirTentative(
        int $reponsesCorrectes,
        int $reponsesMauvaises,
        int $reponsesDonnees,
        int $nonRepondues,
        float $pourcentage,
        array $reponses,
        Tentative $tentative
    ): Tentative {
        $tentative->setDateFin(new DateTime('now'));
        $tentative->setReponsesCorrectes($reponsesCorrectes);
        $tentative->setReponsesMauvaises($reponsesMauvaises);
        $tentative->setReponsesDonnees($reponsesDonnees);
        $tentative->setNonRepondues($nonRepondues);
        $tentative->setPourcentage($pourcentage);
        $tentative->setReponsesUtilisateur($reponses);
        $em = $this->getEntityManager();
        $em->persist($tentative);
        $em->flush();

        // Attribuer un badge "80%" si le score est >= 80 et éviter les doublons
        if ($pourcentage >= 80) {
            $user = $tentative->getUtilisateur();
            $quiz = $tentative->getQuiz();
            if ($user && $quiz) {
                $conn = $em->getConnection();
                // Vérification rapide d'existence (évite un doublon unique)
                $exists = $conn->fetchOne(
                    'SELECT 1 FROM user_badge WHERE utilisateur_id = :u AND quiz_id = :q AND title = :t LIMIT 1',
                    ['u' => $user->getId(), 'q' => $quiz->getId(), 't' => 'Badge 80%']
                );
                if (!$exists) {
                    $badge = new UserBadge();
                    $badge->setUtilisateur($user)->setQuiz($quiz)->setTitle('Badge 80%');
                    $em->persist($badge);
                    $em->flush();
                }
            }
        }

        return $tentative;
    }

    // Récupère une tentative avec les infos du quiz associé
    public function trouverAvecQuiz(int $id): ?Tentative
    {
        return $this->createQueryBuilder('t')             // 't' est l'alias de Tentative
            ->leftJoin('t.quiz', 'q')                      // jointure avec l'entité Quiz
            ->addSelect('q')                               // sélectionne aussi les données du quiz
            ->andWhere('t.id = :id')                       // filtre par ID de la tentative
            ->setParameter('id', $id)                      // remplace le paramètre :id
            ->getQuery()                                   // génère la requête
            ->getOneOrNullResult();                        // renvoie un résultat ou null
    }

    // Renvoie une requête pour récupérer les tentatives d’un utilisateur
    public function requeteParUtilisateur(Utilisateur $utilisateur): Query
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.utilisateur = :utilisateur')          // filtre par utilisateur
            ->setParameter('utilisateur', $utilisateur)         // injecte la valeur de l'utilisateur
            ->orderBy('t.dateDebut', 'DESC')                    // trie par date de début décroissante
            ->getQuery();                                       // renvoie la requête
    }

    /**
     * Moyenne globale des pourcentages sur toutes les tentatives.
     */
    public function moyennePourcentage(): float
    {
        $avg = $this->createQueryBuilder('t')
            ->select('AVG(COALESCE(t.pourcentage, 0)) as avgPct')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $avg;
    }

    /**
     * Dernières tentatives (avec utilisateur et quiz) limitées à $limit.
     * @return Tentative[]
     */
    public function derniereListe(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.utilisateur', 'u')->addSelect('u')
            ->leftJoin('t.quiz', 'q')->addSelect('q')
            ->orderBy('t.dateDebut', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
