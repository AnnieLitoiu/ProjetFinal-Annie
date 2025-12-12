<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\TentativeRepository;
use App\Repository\QuizRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    // Liste des utilisateurs + recherche (page HTML)
    #[Route('/admin', name: 'app_admin', methods: ['GET'])]
    #[Route('/admin/utilisateurs', name: 'admin_users', methods: ['GET'])]
    public function users(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        TentativeRepository $tentativeRepository
    ): Response {
        $view = (string) $request->query->get('view', 'dashboard');
        $q = trim((string) $request->query->get('q', ''));

        if ($view === 'list') {
            $users = $utilisateurRepository->searchByQuery($q);
            return $this->render('admin/index.html.twig', [
                'q' => $q,
                'users' => $users,
            ]);
        }

        $totalUsers = $utilisateurRepository->count([]);
        $totalTentatives = (int) $tentativeRepository
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $avgPct = $tentativeRepository->moyennePourcentage();
        $top5 = array_slice($utilisateurRepository->leaderboard(''), 0, 3);
        $lastAttempts = $tentativeRepository->derniereListe(5);

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalTentatives' => $totalTentatives,
            'avgPct' => $avgPct,
            'top5' => $top5,
            'lastAttempts' => $lastAttempts,
            'q' => $q,
        ]);
    }

    #[Route('/admin/leaderboard/search', name: 'admin_leaderboard_search', methods: ['GET'])]
    public function leaderboardSearch(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        $rows = $utilisateurRepository->leaderboard($q);

        $payload = [];
        foreach ($rows as $row) {
            $u = $row['user'];
            $payload[] = [
                'id' => $u->getId(),
                'email' => $u->getEmail(),
                'prenom' => $u->getPrenom(),
                'nom' => $u->getNom(),
                'bestPct' => $row['bestPct'],
                'attempts' => $row['attempts'],
            ];
        }

        return $this->json(['rows' => $payload]);
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

    #[Route('/admin/leaderboard', name: 'admin_leaderboard', methods: ['GET'])]
    public function leaderboard(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $rows = $utilisateurRepository->leaderboard($q);

        return $this->render('admin/leaderboard.html.twig', [
            'q' => $q,
            'rows' => $rows,
        ]);
    }

    #[Route('/admin/quizzes', name: 'admin_quizzes', methods: ['GET'])]
    public function quizzes(QuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->findBy([], ['id' => 'ASC']);

        return $this->render('admin/quizzes.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }

    // Recherche AJAX d'utilisateurs pour la liste admin
    #[Route('/admin/utilisateurs/search', name: 'admin_users_search', methods: ['GET'])]
    public function searchUsersAjax(
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $q = trim((string) $request->query->get('q', ''));
        $users = $utilisateurRepository->searchByQuery($q);

        $payload = [];
        foreach ($users as $u) {
            $payload[] = [
                'id' => $u->getId(),
                'email' => $u->getEmail(),
                'nom' => $u->getNom(),
                'prenom' => $u->getPrenom(),
                'roles' => $u->getRoles(),
                'csrf' => $csrfTokenManager->getToken('delete_user_' . $u->getId())->getValue(),
            ];
        }

        return $this->json(['users' => $payload]);
    }
}
