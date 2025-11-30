<?php

namespace App\Entity;

use App\Repository\TentativeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TentativeRepository::class)]
class Tentative
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $maxTentatives = null;

    #[ORM\Column]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateFin = null;

    #[ORM\ManyToOne(inversedBy: 'tentatives')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $quiz = null;

    #[ORM\Column(nullable: true)]
    private ?int $reponsesCorrectes = null;

    #[ORM\Column(nullable: true)]
    private ?int $reponsesMauvaises = null;

    #[ORM\Column(nullable: true)]
    private ?int $reponsesDonnees = null;

    #[ORM\Column(nullable: true)]
    private ?int $nonRepondues = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $pourcentage = null;

    #[ORM\Column(nullable: true)]
    private ?array $reponsesUtilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'tentatives')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreQuestions = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempsAlloueSecondes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxTentatives(): ?int
    {
        return $this->maxTentatives;
    }

    public function setMaxTentatives(int $maxTentatives): static
    {
        $this->maxTentatives = $maxTentatives;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getReponsesCorrectes(): ?int
    {
        return $this->reponsesCorrectes;
    }

    public function setReponsesCorrectes(?int $reponsesCorrectes): static
    {
        $this->reponsesCorrectes = $reponsesCorrectes;

        return $this;
    }

    public function getReponsesMauvaises(): ?int
    {
        return $this->reponsesMauvaises;
    }

    public function setReponsesMauvaises(?int $reponsesMauvaises): static
    {
        $this->reponsesMauvaises = $reponsesMauvaises;

        return $this;
    }

    public function getReponsesDonnees(): ?int
    {
        return $this->reponsesDonnees;
    }

    public function setReponsesDonnees(?int $reponsesDonnees): static
    {
        $this->reponsesDonnees = $reponsesDonnees;

        return $this;
    }

    public function getNonRepondues(): ?int
    {
        return $this->nonRepondues;
    }

    public function setNonRepondues(?int $nonRepondues): static
    {
        $this->nonRepondues = $nonRepondues;

        return $this;
    }

    public function getPourcentage(): ?string
    {
        return $this->pourcentage;
    }

    public function setPourcentage(?string $pourcentage): static
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }

    public function getReponsesUtilisateur(): ?array
    {
        return $this->reponsesUtilisateur;
    }

    public function setReponsesUtilisateur(?array $reponsesUtilisateur): static
    {
        $this->reponsesUtilisateur = $reponsesUtilisateur;

        return $this;
    }

    public function formatDuration(): ?array
    {
        $seconds = max(0, $this->getDateFin()->getTimestamp() - $this->getDateDebut()->getTimestamp());
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        // 00:12 si < 1h, sinon 1:02:33
        $label = $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%02d:%02d', $m, $s);

        return ['seconds' => $seconds, 'label' => $label];
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getNombreQuestions(): ?int
    {
        return $this->nombreQuestions;
    }

    public function setNombreQuestions(?int $nombreQuestions): static
    {
        $this->nombreQuestions = $nombreQuestions;
        return $this;
    }

    public function getTempsAlloueSecondes(): ?int
    {
        return $this->tempsAlloueSecondes;
    }

    public function setTempsAlloueSecondes(?int $tempsAlloueSecondes): static
    {
        $this->tempsAlloueSecondes = $tempsAlloueSecondes;
        return $this;
    }
}
