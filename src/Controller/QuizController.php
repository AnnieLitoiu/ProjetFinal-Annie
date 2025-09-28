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

final class QuizController extends AbstractController
{
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(Request $req, QuizRepository $rep, NiveauRepository $repNiveau): Response
    {
        $idNiveau = $req->get('niveau');

        $vars = ['quiz' => $repNiveau->find($idNiveau)->getQuizzes()];

        // $vars = ['quiz' => $rep->findBy(['niveau' => $idNiveau])];

        return $this->render('quiz/liste.html.twig', $vars);
    }

    #[Route('/quiz/{id}', name: 'app_details_quiz')]
    public function detailsQuiz(Request $req, QuizRepository $rep): Response
    {
        $idQuiz = $req->get('id');

        
        $tentative = new Tentative();
        $formTentative = $this->createForm(
            TentativeType::class,
            $tentative,
            array(
                'action' => $this->generateURL ("quiz_tentative_commencer"),
                'method' => 'POST',
                'quiz_id' => $idQuiz
            )
        );
        $vars = ['quiz' => $rep->find($idQuiz), 'formTentative' => $formTentative->createView()];

        return $this->render('quiz/details-quiz.html.twig', $vars);
    }

    #[Route('/tentative/commencer', name: 'quiz_tentative_commencer')]
    public function insertTentative(): Response
    {
        $vars = [];
        return $this->render('quiz/tentative-commencer.html.twig', $vars);
    }                                                                                                                                       
}
