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
        // Rendu de la page
        $response = $this->render('accueil/index.html.twig');

        // 🔒 Désactivation du cache navigateur (DEV / debug)
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    #[Route('/accueil/list-niveaux', name: 'accueil_list_niveaux')]
    public function listNiveaux(NiveauRepository $rep): Response
    {
        $vars = [
            'niveaux' => $rep->findAll()
        ];

        return $this->render('accueil/list-niveaux.html.twig', $vars);
    }
}
