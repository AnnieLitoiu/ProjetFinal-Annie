<?php

namespace App\DataFixtures;

use App\Entity\Niveau;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NiveauFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $niveau = new Niveau();
        $niveau->setNom("Débutant");
        $niveau->setDescription("description debutant");
        $this->addReference("niveau_debutant", $niveau);
        $manager->persist($niveau);

        $niveau = new Niveau();
        $niveau->setNom("Intermédiare");
        $niveau->setDescription("description intermediaire");
        $this->addReference("niveau_intermediaire", $niveau);
        $manager->persist($niveau);

        $niveau = new Niveau();
        $niveau->setNom("Avancé");
        $niveau->setDescription("description avance");
        $this->addReference("niveau_avance", $niveau);
        $manager->persist($niveau);

        $manager->flush();
    }
}
