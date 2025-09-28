<?php

namespace App\Controller;

use App\Repository\NiveauRepository;
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
    public function listNiveaux(NiveauRepository $rep): Response
    {   
        $vars = ['niveaux' => $rep->findAll()];

        return $this->render('accueil/list-niveaux.html.twig', $vars);
    }
}
