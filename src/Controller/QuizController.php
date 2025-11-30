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
    ): Response {
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
    #[Route('/quiz/{id}/start', name: 'app_start_quiz', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function startQuiz(
        Request $req,
        TentativeRepository $repTentative,
        QuizRepository $repQuiz,
        \App\Repository\QuestionRepository $repQuestion
    ): Response {
        $idQuiz = (int) $req->get('id');
        $quiz = $repQuiz->find($idQuiz);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz introuvable');
        }

        if ($req->isMethod('GET')) {
            // Affiche l'écran de démarrage (choix 5/10/15 questions)
            return $this->render('quiz/demarrer.html.twig', [
                'quiz' => $quiz,
                'nombre_par_defaut' => 10,
                'temps_par_question' => 30,
            ]);
        }

        // POST: lecture du choix utilisateur
        $nombre = (int) $req->request->get('nombre_questions', 10);
        if (!in_array($nombre, [5, 10, 15], true)) {
            $nombre = 10;
        }
        $tempsParQuestion = 30; // secondes/question (configurable)
        $tempsAlloue = $nombre * $tempsParQuestion;

        // Tirage aléatoire des N questions à partir du quiz
        $idsQuestions = $repQuestion->tirerAleatoireIdsParQuiz($idQuiz, $nombre);
        if (count($idsQuestions) === 0) {
            $this->addFlash('warning', 'Aucune question disponible pour ce quiz.');
            return $this->redirectToRoute('app_quiz_liste', ['niveau' => $quiz->getNiveau()->getId()]);
        }

        // Crée la tentative en enregistrant le nombre et le temps alloué
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        $tentative = $repTentative->saveTentative($quiz, $utilisateur, $nombre, $tempsAlloue);

        // Stocke la sélection des questions en session, isolée par tentative
        $session = $req->getSession();
        $session->set('quiz_question_ids' . $tentative->getId(), $idsQuestions);

        return $this->redirectToRoute('app_quiz_jouer', ['id' => $tentative->getId(), 'page' => 1]);
    }
}
