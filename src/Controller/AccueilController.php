<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_accueil')]
    public function index(): Response
    {
        
<<<<<<< HEAD
        $adresse = ['rue' => 'Avenue Cicéron',
                    'numero' => 100,
                    'codePostal' => '1000'
        ];

        $vars = ['nom' => 'Tommy', // passage de variable simple
                'hobby' => 'dormir',
                'dateNaissance' => new \DateTime ("2016-5-16"), // passage d'un objet
                'adresse' => $adresse
        ]; 
=======
        $adresse = [
            'rue' => 'Avenue Ciceron',
            'numero' => 100,
            'codePostal' => '1000'
        ];

        $vars = [
            'nom' => 'Tommy',
            'hobby' => 'dormir',
            'dateNaissance' => new \DateTime("2019-5-16"),
            'adresse' => $adresse
        ];

>>>>>>> login-pass
        return $this->render('accueil/index.html.twig', $vars);
    }
}
