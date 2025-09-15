<?php

namespace App\DataFixtures;

use App\Entity\Tentative as TentativeEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class TentativeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('ro_RO');

        // Get all available quizzes
        $quizzes = $manager->getRepository(\App\Entity\Quiz::class)->findAll();

        // If no quizzes exist, you might want to create some first
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
}
