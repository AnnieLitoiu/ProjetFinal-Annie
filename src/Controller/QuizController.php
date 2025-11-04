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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class QuizController extends AbstractController
{
    // Liste paginée des quiz pour un niveau donné
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(
        Request $req, 
        QuizRepository $rep, 
        NiveauRepository $repNiveau, 
        PaginatorInterface $paginator
    ): Response 
    {
        $idNiveau = $req->get('niveau');
        
        // Requête Doctrine (QueryBuilder) pour récupérer les quiz du niveau
        $query = $rep->requeteParNiveauId((int) $idNiveau);
        
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
        // Récupération de l'id du quiz depuis la requête (URL)
        $idQuiz = $req->get('id');
        // Recherche du quiz correspondant à cet id dans la base de données
        $quiz = $repQuiz->find($idQuiz);
        // Récupération de l'id de l'utilisateur
        $utilisateur = $this->getUser();
        
        // Création/enregistrement d'une nouvelle tentative associée au quiz
        $tentative = $rep->saveTentative($quiz, $utilisateur);

        // Redirection vers la route qui affiche les questions (paginées)
        return $this->redirectToRoute('app_quiz_jouer',['id' => $tentative->getId()]);
    }


}
