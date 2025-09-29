<?php

namespace App\Controller;

use App\Entity\Tentative;
use App\Form\TentativeType;
use App\Repository\NiveauRepository;
use App\Repository\QuizRepository;
use App\Repository\TentativeRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class QuizController extends AbstractController
{
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(
        Request $req,
        QuizRepository $rep,
        NiveauRepository $repNiveau,
        PaginatorInterface $paginator
    ): Response {

        $idNiveau = $req->get('niveau');
        
        
        // Requête Doctrine DQL via le repository
        $query = $rep->createQueryBuilder('q')
        ->where('q.niveau = :niveau')
        ->setParameter('niveau', $idNiveau)
        ->getQuery();
        
        // Paginer la requête
        $pagination = $paginator->paginate(
            $query,
            $req->query->getInt('page', 1), 5
        );
    
        return $this->render('quiz/liste.html.twig', [
            'quizzes' => $pagination,
            'niveau' => $repNiveau->find($idNiveau), 
        ]);
    }

    #[Route('/quiz/{id}/start', name: 'app_start_quiz', requirements: ['id' => '\d+'])]
    public function startQuiz(Request $req, TentativeRepository $rep, QuizRepository $repQuiz): Response
    {
        $idQuiz = $req->get('id');
        $quiz = $repQuiz->find($idQuiz);
        $tentative = $rep->saveTentative($quiz);

        return $this->redirectToRoute('app_quiz_jouer',['id' => $tentative->getId()]);
    }

    #[Route('/quiz/tentative/{id}', name: 'app_quiz_jouer')]
    public function executerQuestion(Request $req, TentativeRepository $rep): Response
    {
        $idTentative = $req->get('id');
        $tentative = $rep->find($idTentative);
        $questions = $tentative->getQuiz()->getQuestions();   
        $vars = ['questions' => $questions];

        return $this->render ("quiz/quiz_executer_question.html.twig", $vars);
    }
}

