<?php

namespace App\Controller;

use App\Entity\Tentative;
use App\Form\TentativeType;
use App\Repository\NiveauRepository;
use App\Repository\QuizRepository;
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

    #[Route('/detail/quiz/{id}', name: 'app_detail_quiz', requirements: ['id' => '\d+'])]
    public function detailQuiz(Request $req, QuizRepository $rep): Response
    {

        $idQuiz = $req->get('id');
        $vars = ['quiz' => $rep->find($idQuiz)];

        dd($vars);

        // return $this->render('quiz/detail_quiz.html.twig', $vars);
    }

    #[Route('/executer/question/{id}/{numero_question}', name: 'app_quiz_executer', defaults: ['id_question' => null])]
    public function executerQuestion(Request $req, QuizRepository $rep): Response
    {
        $idQuiz = $req->get('id');
        $numeroQuestion = $req->get('numero_question');

        // obtenir le quiz
        $quiz = $rep->find($idQuiz);

        // obtenir toutes les questions et reponses
        $questions = $quiz->getQuestions();
        
        $vars = [];

        // a chaque tour il faut envoyer la question suivante
        if (is_null($numeroQuestion)){
            $vars = [
                'question' => $questions[0],
                'numeroQuestion' => 0
            ];
        }

        return $this->render ("quiz_executer_question.html.twig", $vars);
    }


}

