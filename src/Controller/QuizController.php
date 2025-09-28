<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuizController extends AbstractController
{
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(Request $req, QuizRepository $rep): Response
    {
        $idNiveau = $req->get('niveau');
       
        $vars = ['quiz' => $rep->findBy(['niveau' => $idNiveau])];

        return $this->render('quiz/liste.html.twig', $vars);
    }
}
