<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Quiz;
use App\Entity\Niveau;
use App\Entity\Question;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class QuizQuestionsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        

        $niveauxPossiblesString = [
            'niveau_debutant',
            'niveau_intermediaire',
            'niveau_avance',
        ];

        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 20; $j++) {
                $quiz = new Quiz();
                $quiz->setTitre($faker->sentence);
                $choixNiveau = $niveauxPossiblesString[$i];
                $quiz->setNiveau($this->getReference(
                    $choixNiveau,
                    Niveau::class
                ));
                
                // creer rajouter les auestions pour chaque quiz
                for ($k =0; $k < 20; $k++){
                    $question = new Question();
                    $question->setEnonce($faker->sentence);
                    $question->setQuiz($this->setReference("question" . $k, $question));
                    // rqjouter la question au quiz courant
                    $quiz->addQuestion($question);                    
                }
                $manager->persist($quiz);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return ([NiveauFixtures::class]);
    }
}
