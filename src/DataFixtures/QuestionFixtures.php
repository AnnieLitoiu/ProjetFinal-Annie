<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\Quiz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class QuestionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $faker = Faker\Factory::create('ro_RO');
       $repository = $manager->getRepository(Quiz::class);
       $quiz = $repository->findAll();

       for ($i =0; $i < 100; $i++){
        $question = new Question();
        $question->setEnonce($faker->sentence);
        $question->setQuiz($faker->randomElement($quiz));
        $manager->persist($question);
       }

        $manager->flush();
    }
}
