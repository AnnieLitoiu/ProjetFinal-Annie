<?php

namespace App\Controller;

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

        $vars = ['quiz' => $rep->find($idQuiz)];

        return $this->render('quiz/details-quiz.html.twig', $vars);
    }

}
