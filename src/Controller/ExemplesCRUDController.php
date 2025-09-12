<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ExemplesCRUDController extends AbstractController{
    #[Route("/produit/insert")]
    public function insererProduit (ManagerRegistry $doctrine){
        $p1 = new Produit ();
        $p1->setNom ("Chocolat");
        $p1->setPrix (3);

        $p2 = new Produit ();
        $p2->setNom ("Oranges");
        $p2->setPrix (2);
        // insert entity dans la BD
        $manager = $doctrine->getManager();
        $manager->persist($p1);
        $manager->persist($p2);
        
        $manager->flush();
        
        $p1->setNom("Pommes");
        

        return new Response ("bonjour");
    }

}