<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UtilisateurFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        
        // 10 utilisateurs standard
        for ($i = 1; $i <= 10; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setEmail("user" . $i . "@gmail.com");
            $utilisateur->setRoles(['ROLE_USER']);
            $utilisateur->setPassword(
                $this->hasher->hashPassword($utilisateur, "password" . $i)
            );

            // Ajout nom + prénom en français
            $utilisateur->setPrenom($faker->firstName());
            $utilisateur->setNom($faker->lastName());

            $this->addReference("utilisateur" . $i, $utilisateur);
            $manager->persist($utilisateur);
        }

        // 1 administratrice : Annie
        $utilisateur = new Utilisateur();
        $utilisateur->setEmail("annie@gmail.com");
        $utilisateur->setRoles(['ROLE_ADMIN']);
        $utilisateur->setPassword(
            $this->hasher->hashPassword($utilisateur, "hello13")
        );

        $utilisateur->setPrenom("Annie");
        $utilisateur->setNom("Litoiu"); 

        $this->addReference("admin1", $utilisateur);
        $manager->persist($utilisateur);

        $manager->flush();
    }
}
