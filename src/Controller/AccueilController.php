<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    #[Route('/accueil/welcome', name: 'accueil_welcome')]
    public function index(): Response
    { 
        return $this->render('accueil/index.html.twig');
    }

    #[Route('/accueil/list-niveaux', name: 'accueil_list_niveaux')]
    public function listNiveaux(): Response
    {   
        $niveaux = ['debutant' => 'Débutant',
                    'intermediaire' => 'Intermédiaire',
                    'avance' => 'Avancé'];

        return $this->render('accueil/list-niveaux.html.twig', ['niveaux' => $niveaux]);
    }
}
