<?php

namespace App\Repository;

use App\Entity\Quiz;
use App\Entity\Tentative;
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
        ?int $nombreQuestions = null,
        ?int $tempsAlloueSecondes = null
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
}
