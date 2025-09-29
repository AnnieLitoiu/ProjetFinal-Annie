<?php

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\Reponse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class ReponseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
      $faker = Faker\Factory::create('fr_FR');
      $repository = $manager->getRepository(Question::class);
      $question = $repository->findAll();
      
        for ($i = 0; $i < 50; $i++){
            $reponse = new Reponse();
            $reponse->setTexte($faker->sentence);
            $reponse->setEstCorrecte($faker->boolean);
            $reponse->setQuestion($faker->randomElement($question));
            $manager->persist($reponse);
        }

        $manager->flush();
    }
}
