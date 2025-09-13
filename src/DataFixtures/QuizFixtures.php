<?php

namespace App\DataFixtures;

use App\Entity\Quiz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class QuizFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('ro_RO');
        
        for ($i = 0; $i < 10; $i++){
            $quiz = new Quiz();
            $quiz->setTitre($faker->sentence);
            $quiz->setNiveau($faker->randomDigit);
            $manager->persist($quiz);
        }
         
        $manager->flush();
    }
}
