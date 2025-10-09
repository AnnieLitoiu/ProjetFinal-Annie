<?php

namespace App\Repository;

use App\Entity\Reponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reponse>
 */
class ReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    /**
     * @param int[] $idsQuestions
     * @return array<int,string> [id_question => texteBonneRéponse]
     */
    public function trouverTexteBonnesReponses(array $idsQuestions): array
    {
        if (!$idsQuestions) return [];

        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.question) AS id_question, r.texte AS texte')
            ->andWhere('r.question IN (:ids)')
            ->andWhere('r.estCorrecte = true')
            ->setParameter('ids', $idsQuestions)
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id_question']] = $row['texte'];
        }
        return $map;
    }
}
