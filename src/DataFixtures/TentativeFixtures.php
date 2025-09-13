<?php

namespace App\DataFixtures;

use App\Entity\Quiz;
use App\Entity\Tentative;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class TentativeFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UtilisateurFixtures::class,
            QuizFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('ro_RO');
        $repository = $manager->getRepository(Utilisateur::class);
        $utilisateurs = $repository->findAll();
        $repository = $manager->getRepository(Quiz::class);
        $quiz = $repository->findAll();

        for ($i = 0; $i < 50; $i++){
            $tentative = new Tentative();
            $tentative->setMaxTentatives($faker->randomDigit);
            $tentative->setDateDebut($faker->dateTime);
            $tentative->setDateFin($faker->dateTime);
            $tentative->setScore($faker->randomDigit);
            $tentative->setUtilisateur($faker->randomElement($utilisateurs));
            $tentative->setQuiz($faker->randomElement($quiz));
            $manager->persist($tentative);
        }

        $manager->flush();
    }
}
