<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilUtilisateurController extends AbstractController
{
    #[Route('/mon-activite', name: 'utilisateur_activity')]
    public function activity(): Response
    {
        // On récupère l'utilisateur connecté. La méthode getUser() renvoie l'objet Utilisateur
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        // Si personne n'est connecté, on redirige vers la page de connexion
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        // Récupère les tentatives de quiz grâce à la relation OneToMany dans l'entité Utilisateur
        $tentatives = $utilisateur->getTentatives();

        return $this->render('profil_utilisateur/mon_activite.html.twig', [
            'tentatives' => $tentatives,
            'utilisateur' => $utilisateur,
        ]);
    }
}
