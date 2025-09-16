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
       return $this->redirectToRoute('app_forms_affiche_quiz'); 
    }


    #[Route('/forms/insert/quiz', name: 'app_forms_insert_quiz')]
    public function insertQuiz(Request $req, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();

        $formQuiz = $this->createForm(QuizType::class, $quiz);
        $formQuiz->handleRequest($req);

        if ($formQuiz->isSubmitted()) {

            $em->persist($quiz);
            $em->flush();

            return $this->redirectToRoute('app_forms_affiche_quiz');
        } else {
            $vars = ['formQuiz' => $formQuiz];
            return $this->render('forms/affiche_form_insert_quiz.html.twig', $vars);
        }
    }

    #[Route('/forms/affiche/quiz', name: 'app_forms_affiche_quiz')]
    public function afficherQuiz(EntityManagerInterface $em): Response
    {
        $repository = $em->getRepository(Quiz::class);
        $quiz = $repository->findAll();
        $vars = ['quiz'=> $quiz];

        return $this->render('forms/affiche_quiz.html.twig', $vars);
    }
}
