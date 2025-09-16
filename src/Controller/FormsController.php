<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Form\QuizType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FormsController extends AbstractController
{
    #[Route('/forms', name: 'app_forms')]
    public function afficherForm(): Response
    {
        $formQuiz = $this->createForm(QuizType::class);
        $vars = ['formQuiz' => $formQuiz];

        return $this->render('forms/afficher_form.html.twig', $vars);
    }


    #[Route('/forms/insert/quiz', name:'app_forms_insert_quiz')]
    public function insertQuiz(Request $req, EntityManagerInterface $em):Response{
        $quiz = new Quiz();

        $formQuiz = $this->createForm (QuizType::class, $quiz);

        if ($formQuiz->isSubmitted()){
            $em->persist($quiz);
            $em->flush();
            return $this->redirectToRoute('app_forms_resultat_traitement_form_insert');
        }
        else {
            $vars = ['formQuiz' => $formQuiz];
            return $this->render ('forms/affiche_form_insert_quiz.html.twig', $vars);
        }
    }

}