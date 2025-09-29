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
        
        for ($i = 1; $i <= 5; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setEmail("user" . $i . "@gmail.com");
            $utilisateur->setRoles(['ROLE_USER']);
            $utilisateur->setPassword($this->hasher->hashPassword($utilisateur, "password" . $i));
            
            // créer une référence qui sera accéssible depuis partout (toutes les fixtures)
            $this->addReference("utilisateur" . $i, $utilisateur);
            
            $manager->persist($utilisateur);
        }
        for ($i = 1; $i <= 1; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setEmail("ilinca" . $i . "@gmail.com");
            $utilisateur->setRoles(['ROLE_ADMIN']);
            $utilisateur->setPassword($this->hasher->hashPassword($utilisateur, "cucurigu" . $i));
            $manager->persist($utilisateur);

            // créer une référence qui sera accéssible depuis partout (toutes les fixtures)
            $this->addReference("admin" . $i, $utilisateur);
        }
        $manager->flush();

    }
}