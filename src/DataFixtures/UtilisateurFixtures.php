<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class UtilisateurFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('ro_RO');
        
        // for ($i = 0; $i < 10; $i++) {
        //     $utilisateur = new Utilisateur();
        //     $utilisateur->setNom("Annie " . $i);
        //     $utilisateur->setRoles(["admin" , "user"]);
        //     $utilisateur->setEmail("annie" . $i . "@gmail.com");
        //     $utilisateur->setPseudo("Annie" . $i);
        //     $utilisateur->setMotDePasse("Annie#123" . $i);
        //     $manager->persist($utilisateur);
        // }
        for ($i = 0; $i < 10; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($faker->name);
            $utilisateur->setRoles($faker->randomElements(["admin" , "user"]));
            $utilisateur->setEmail($faker->email);
            $utilisateur->setPseudo($faker->userName);
            $utilisateur->setMotDePasse($faker->password);
            $manager->persist($utilisateur);
        }

        $manager->flush();
    }
}
