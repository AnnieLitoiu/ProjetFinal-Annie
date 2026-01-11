<?php

namespace App\Repository;

use App\Entity\UserBadge;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBadge>
 */
class UserBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBadge::class);
    }

    /**
     * @return UserBadge[]
     */
    public function findByUser(Utilisateur $user): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.quiz', 'q')->addSelect('q')
            ->andWhere('b.utilisateur = :u')
            ->setParameter('u', $user)
            ->orderBy('b.earnedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
