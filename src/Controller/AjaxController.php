<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AjaxController extends AbstractController
{
    // Recherche AJAX des utilisateurs pour l'admin (retourne du JSON)
    #[Route('/admin/utilisateurs/search', name: 'admin_users_search', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')] 
    public function searchUsers(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        // Terme de recherche dans l'URL (?q=...)
        $q = trim((string) $request->query->get('q', ''));
        $users = $utilisateurRepository->searchByQuery($q);

        // On prépare un tableau simple pour le JSON
        $data = array_map(function (Utilisateur $u) {
            return [
                'id' => $u->getId(),
                'email' => $u->getEmail(),
                'prenom' => $u->getPrenom(),
                'nom' => $u->getNom(),
                'roles' => $u->getRoles(),
                // Jeton CSRF pour sécuriser un bouton "supprimer" côté JS
                'csrf' => $this->container
                    ->get('security.csrf.token_manager')
                    ->getToken('delete_user_' . $u->getId())
                    ->getValue(),
            ];
        }, $users);

        return $this->json(['users' => $data]);
    }
}
