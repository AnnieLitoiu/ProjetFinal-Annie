<?php

namespace App\Controller;

use App\Repository\NiveauRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_home_redirect')]
    public function homeRedirect(): Response
    {
        return $this->redirectToRoute('accueil_welcome');
    }

    #[Route('/accueil/welcome', name: 'accueil_welcome')]
    public function index(): Response
    { 
        return $this->render('accueil/index.html.twig');
    }

    #[Route('/accueil/list-niveaux', name: 'accueil_list_niveaux')]
    public function listNiveaux(NiveauRepository $rep): Response
    {   
        // Récupération de tous les niveaux via le repository Doctrine
        $vars = ['niveaux' => $rep->findAll()];

        // Passage de la liste des niveaux au template “list-niveaux”
        return $this->render('accueil/list-niveaux.html.twig', $vars);
    }
}
