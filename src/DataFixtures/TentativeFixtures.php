<?php

namespace App\DataFixtures;

use Faker;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Tentative as TentativeEntity;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Repository\TentativeRepository;

class TentativeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $quizzes = $manager->getRepository(\App\Entity\Quiz::class)->findAll();

        if (empty($quizzes)) {
            throw new \RuntimeException('No quizzes found. Please load Quiz fixtures first.');
        }

        for ($i = 0; $i < 5; $i++) {
            $tentative = new TentativeEntity();
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
            
            $manager->persist($tentative);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return ([QuizQuestionsReponsesFixtures::class]);
    }
}
