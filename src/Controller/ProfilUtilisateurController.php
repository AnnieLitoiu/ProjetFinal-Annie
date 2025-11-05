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
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        // grâce à ta relation OneToMany
        $tentatives = $utilisateur->getTentatives();

        return $this->render('profil_utilisateur/activity.html.twig', [
            'tentatives' => $tentatives,
            'utilisateur' => $utilisateur,
        ]);
    }
}
