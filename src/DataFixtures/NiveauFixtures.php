<?php

namespace App\DataFixtures;

use App\Entity\Niveau;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class NiveauFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $faker = Faker\Factory::create('ro_RO');

        $niveau = new Niveau();
        $niveau->setNom("Débutant");
        $this->addReference("niveau_debutant", $niveau);
        $manager->persist($niveau);

        $niveau = new Niveau();
        $niveau->setNom("Intermediare");
        $this->addReference("niveau_intermediaire", $niveau);
        $manager->persist($niveau);

        $niveau = new Niveau();
        $niveau->setNom("Avancé");
        $this->addReference("niveau_avance", $niveau);
        $manager->persist($niveau);



        $manager->flush();
    }
}
