<?php

namespace App\Repository;

use App\Entity\Quiz;
use App\Entity\Tentative;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    //    /**
    //     * @return Tentative[] Returns an array of Tentative objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tentative
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function saveTentative(Quiz $quiz): Tentative {
        $tentative = new Tentative();
        $tentative->setDateDebut(new DateTime('now'));
        $tentative->setMaxTentatives(self::MAX_TENTATIVES);
        $tentative->setPourcentage(0);
        $tentative->setQuiz($quiz);
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
                    Tentative $tentative): Tentative {
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
}
