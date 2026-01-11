<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserBadgeRepository;

final class ProfilUtilisateurController extends AbstractController
{
    #[Route('/mon-activite', name: 'utilisateur_activity')]
    public function activity(UserBadgeRepository $userBadgeRepository): Response
    {
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }

        $tentatives = $utilisateur->getTentatives();
        $badges = $userBadgeRepository->findByUser($utilisateur);

        return $this->render('profil_utilisateur/mon_activite.html.twig', [
            'tentatives' => $tentatives,
            'utilisateur' => $utilisateur,
            'badges' => $badges,
        ]);
    }
}
