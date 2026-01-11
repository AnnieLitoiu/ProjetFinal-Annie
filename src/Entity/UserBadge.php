<?php

namespace App\Entity;

use App\Repository\UserBadgeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBadgeRepository::class)]
#[ORM\Table(name: 'user_badge')]
#[ORM\UniqueConstraint(name: 'uniq_user_quiz_title', columns: ['utilisateur_id', 'quiz_id', 'title'])]
class UserBadge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\Column(length: 255)]
    private string $title = 'Badge 80%';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $earnedAt;

    public function __construct()
    {
        $this->earnedAt = new \DateTimeImmutable('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }
    public function setUtilisateur(Utilisateur $u): self
    {
        $this->utilisateur = $u;
        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }
    public function setQuiz(Quiz $q): self
    {
        $this->quiz = $q;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $t): self
    {
        $this->title = $t;
        return $this;
    }

    public function getEarnedAt(): \DateTimeImmutable
    {
        return $this->earnedAt;
    }
    public function setEarnedAt(\DateTimeImmutable $dt): self
    {
        $this->earnedAt = $dt;
        return $this;
    }
}
