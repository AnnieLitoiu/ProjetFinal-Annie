<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExempleController extends AbstractController
{
    #[Route('/exemple', name: 'app_exemple')]
    public function index(): Response
    {
        return $this->render('exemple/index.html.twig', [
            'controller_name' => 'ExempleController',
        ]);
    }

    #[Route('/exemple/bonjour')]
    public function bonjour (){
        return $this->render ('exemple/bonjour.html.twig');
    }

    #[Route('/exemple/insert')]
    public function insertCategorie(EntityManagerInterface $manager){
        $categorie1 = new Categorie();
        $categorie1->setNom("boissons");
        $categorie2 = new Categorie();
        $categorie2->setNom("nourriture");
        
        $manager->persist($categorie1);
        $manager->persist($categorie2);
        
        $manager->flush();
        $categorie1->setNom("nada");
        $manager->flush();


        return new Response("Categories insérées");

    }
}