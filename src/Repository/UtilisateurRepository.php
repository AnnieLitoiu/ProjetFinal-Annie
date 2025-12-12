<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return Utilisateur[]
     */
    public function searchByQuery(string $q): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC');

        if ($q !== '') {
            $qb->andWhere('LOWER(u.email) LIKE :q OR LOWER(u.prenom) LIKE :q OR LOWER(u.nom) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, array{user: Utilisateur, bestPct: float, attempts: int}>
     */
    public function leaderboard(string $q = '', ?int $quizId = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.tentatives', 't')
            ->addSelect('COALESCE(MAX(t.pourcentage), 0) AS bestPct')
            ->addSelect('COUNT(t.id) AS attemptsCount');

        if ($q !== '') {
            $qb->andWhere('LOWER(u.email) LIKE :q OR LOWER(u.prenom) LIKE :q OR LOWER(u.nom) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        if ($quizId !== null && $quizId > 0) {
            $qb->andWhere('t.quiz = :quizId')
                ->setParameter('quizId', $quizId);
        }

        $qb->groupBy('u.id')
            ->orderBy('bestPct', 'DESC');

        $rows = $qb->getQuery()->getResult();

        return array_map(
            fn($r) => [
                'user' => $r[0],
                'bestPct' => (float) $r['bestPct'],
                'attempts' => (int) $r['attemptsCount'],
            ],
            $rows
        );
    }
}
