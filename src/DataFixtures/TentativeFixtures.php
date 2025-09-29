<?php

namespace App\DataFixtures;

use Faker;
use App\DataFixtures\QuizFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Tentative as TentativeEntity;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TentativeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $quizzes = $manager->getRepository(\App\Entity\Quiz::class)->findAll();

        if (empty($quizzes)) {
            throw new \RuntimeException('No quizzes found. Please load Quiz fixtures first.');
        }

        for ($i = 0; $i < 50; $i++) {
            $tentative = new TentativeEntity();
            $tentative->setMaxTentatives($faker->randomDigit);
            $tentative->setDateDebut($faker->dateTime);
            $tentative->setDateFin($faker->dateTime);
            $tentative->setScore($faker->randomDigit);
            $tentative->setQuiz($faker->randomElement($quizzes));
            $manager->persist($tentative);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return ([QuizFixtures::class]);
    }
}
