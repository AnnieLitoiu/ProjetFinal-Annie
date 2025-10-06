<?php

namespace App\Controller;

use App\Repository\NiveauRepository;
use App\Repository\QuizRepository;
use App\Repository\TentativeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class QuizController extends AbstractController
{
    // Liste paginée des quiz pour un niveau donné
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(Request $req, QuizRepository $rep, NiveauRepository $repNiveau, PaginatorInterface $paginator): Response 
    {
        $idNiveau = $req->get('niveau');
        
        // Requête Doctrine (QueryBuilder) pour récupérer les quiz du niveau
        $query = $rep->createQueryBuilder('q')
        ->where('q.niveau = :niveau')
        ->setParameter('niveau', $idNiveau)
        ->getQuery();
        
        // Pagination des résultats (5 éléments par page)
        $pagination = $paginator->paginate(
            $query,
            $req->query->getInt('page', 1),
            5
        );
    
        return $this->render('quiz/liste.html.twig', [
            'quizzes' => $pagination,
            'niveau' => $repNiveau->find($idNiveau), 
        ]);
    }

    // Crée une tentative pour un quiz et redirige vers l'écran de jeu (question par question)
    #[Route('/quiz/{id}/start', name: 'app_start_quiz', requirements: ['id' => '\d+'])]
    public function startQuiz(Request $req, TentativeRepository $rep, QuizRepository $repQuiz): Response
    {
        $idQuiz = $req->get('id');
        $quiz = $repQuiz->find($idQuiz);

        // Création/enregistrement d'une nouvelle tentative associée au quiz
        $tentative = $rep->saveTentative($quiz);

        // Redirection vers la route qui affiche les questions (paginées)
        return $this->redirectToRoute('app_quiz_jouer',['id' => $tentative->getId()]);
    }
}
