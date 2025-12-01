<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\TentativeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')] 
final class AdminController extends AbstractController
{
    // Liste des utilisateurs + recherche (page HTML)
    #[Route('/admin', name: 'app_admin', methods: ['GET'])]
    #[Route('/admin/utilisateurs', name: 'admin_users', methods: ['GET'])]
    public function users(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $users = $utilisateurRepository->searchByQuery($q);

        return $this->render('admin/index.html.twig', [
            'q' => $q,
            'users' => $users,
        ]);
    }

    // Détail d'un utilisateur + ses tentatives de quiz
    #[Route('/admin/utilisateurs/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur, TentativeRepository $tentativeRepository): Response
    {
        $tentatives = $tentativeRepository->requeteParUtilisateur($utilisateur)->getResult();

        return $this->render('admin/index.html.twig', [
            'utilisateur' => $utilisateur,
            'tentatives' => $tentatives,
        ]);
    }

    // Suppression d'un utilisateur depuis l'admin
    #[Route('/admin/utilisateurs/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $em
    ): RedirectResponse {
        $token = (string) $request->request->get('_token');

        // Vérifie le jeton CSRF avant de supprimer
        if (!$this->isCsrfTokenValid('delete_user_' . $utilisateur->getId(), $token)) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_users');
        }

        // On supprime d'abord les tentatives liées pour éviter les erreurs de clé étrangère
        foreach ($utilisateur->getTentatives() as $tentative) {
            $em->remove($tentative);
        }

        $em->remove($utilisateur);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_users');
    }
}
