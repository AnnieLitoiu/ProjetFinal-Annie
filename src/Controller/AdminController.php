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

    #[Route('/admin/utilisateurs/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur, TentativeRepository $tentativeRepository): Response
    {
        $tentatives = $tentativeRepository->requeteParUtilisateur($utilisateur)->getResult();

        return $this->render('admin/index.html.twig', [
            'utilisateur' => $utilisateur,
            'tentatives' => $tentatives,
        ]);
    }

    #[Route('/admin/utilisateurs/search', name: 'admin_users_search', methods: ['GET'])]
    public function searchAjax(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $users = $utilisateurRepository->searchByQuery($q);

        $data = array_map(function (Utilisateur $u) {
            return [
                'id' => $u->getId(),
                'email' => $u->getEmail(),
                'prenom' => $u->getPrenom(),
                'nom' => $u->getNom(),
                'roles' => $u->getRoles(),
                'csrf' => $this->container->get('security.csrf.token_manager')->getToken('delete_user_' . $u->getId())->getValue(),
            ];
        }, $users);

        return $this->json(['users' => $data]);
    }

    #[Route('/admin/utilisateurs/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $em
    ): RedirectResponse {
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_user_' . $utilisateur->getId(), $token)) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_users');
        }

        // supprimer d'abord les tentatives pour éviter les contraintes d'intégrité
        foreach ($utilisateur->getTentatives() as $tentative) {
            $em->remove($tentative);
        }
        $em->remove($utilisateur);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé.');
        return $this->redirectToRoute('admin_users');
    }
}
