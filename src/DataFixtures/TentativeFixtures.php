<?php

namespace App\DataFixtures;

use Faker;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Tentative as TentativeEntity;
use App\Entity\Utilisateur;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Repository\TentativeRepository;

class TentativeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        // Récupère tous les quiz : chaque tentative doit être liée à un quiz existant
        $quizzes = $manager->getRepository(\App\Entity\Quiz::class)->findAll();

        if (empty($quizzes)) {
            // Sécurité : on stoppe si les quiz n'ont pas encore été générés
            throw new \RuntimeException('Aucun quiz trouvé. Veuillez charger les fixtures de Quiz avant.');
        }

        for ($i = 0; $i < 25; $i++) {
            $tentative = new TentativeEntity();

            // Valeurs aléatoires réalistes pour simuler des tentatives
            $tentative->setMaxTentatives(TentativeRepository::MAX_TENTATIVES);
            $tentative->setDateDebut($faker->dateTime);
            $tentative->setDateFin($faker->dateTime);
            $tentative->setQuiz($faker->randomElement($quizzes));
            $tentative->setReponsesCorrectes($faker->randomDigit);
            $tentative->setReponsesMauvaises($faker->randomDigit);
            $tentative->setReponsesDonnees($faker->randomDigit);
            $tentative->setNonRepondues($faker->randomDigit);
            $tentative->setPourcentage($faker->randomDigit);
            $tentative->setReponsesUtilisateur([$faker->randomDigit()]);

            // Associe une tentative à un utilisateur déjà créé (fixtures Utilisateur)
            $tentative->setUtilisateur($this->getReference("utilisateur" . rand(1,5), Utilisateur::class));
            
            $manager->persist($tentative);
        }

        $manager->flush();
    }

    // Cette fixture dépend des quiz, donc elle doit s'exécuter après
    public function getDependencies(): array
    {
        return ([QuizQuestionsReponsesFixtures::class]);
    }
}
