<?php

namespace App\DataFixtures;

use App\Entity\Quiz;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Niveau;
use Faker;

class QuizFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('ro_RO');

        $niveauxPossiblesString = [
            'niveau_debutant',
            'niveau_intermediaire',
            'niveau_avance',
        ];

        for ($i = 0; $i < 10; $i++) {
            $quiz = new Quiz();
            $quiz->setTitre($faker->sentence);
            $choixNiveau = $niveauxPossiblesString[rand(0, 2)];
            $quiz->setNiveau($this->getReference(
                $choixNiveau,
                Niveau::class
            ));
            $manager->persist($quiz);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {

        return ([NiveauFixtures::class]);
    }
}
