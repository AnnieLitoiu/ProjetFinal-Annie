<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Produit;
use Faker;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_BE');

        for ($i = 0; $i < 50; $i++) {
            $produit = new Produit();
            $produit->setNom($faker->word());
            $produit->setPrix(rand(30, 100));
            $manager->persist($produit);
        }
        $manager->flush();
    }
}


