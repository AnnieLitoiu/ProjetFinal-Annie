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

#[IsGranted('ROLE_USER')] // Ce contrôleur est réservé aux utilisateurs connectés
final class QuizController extends AbstractController
{
    // Affiche une liste paginée des quiz pour un niveau donné
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(
        Request $req,
        QuizRepository $rep,
        NiveauRepository $repNiveau,
        PaginatorInterface $paginator
    ): Response {
        $idNiveau = $req->get('niveau');

        // Requête Doctrine qui récupère les quiz appartenant à ce niveau
        $query = $rep->requeteParNiveauId((int) $idNiveau);

        // Pagination : on limite à 5 quiz par page
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

    // Prépare et démarre une tentative de quiz (choix du nombre de questions)
    #[Route('/quiz/{id}/start', name: 'app_start_quiz', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function startQuiz(
        Request $req,
        TentativeRepository $repTentative,
        QuizRepository $repQuiz,
        \App\Repository\QuestionRepository $repQuestion
    ): Response {
        $idQuiz = (int) $req->get('id');
        $quiz = $repQuiz->find($idQuiz);

        // Si l'ID du quiz n'existe pas, on déclenche une erreur 404
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz introuvable');
        }

        // Affichage de la page permettant de choisir 5, 10 ou 15 questions
        if ($req->isMethod('GET')) {
            return $this->render('quiz/demarrer.html.twig', [
                'quiz' => $quiz,
                'nombre_par_defaut' => 10, // valeur proposée par défaut
                'temps_par_question' => 30, // durée attribuée par question (en secondes)
            ]);
        }

        // Lecture du choix utilisateur (5, 10 ou 15 questions)
        $nombre = (int) $req->request->get('nombre_questions', 10);
        if (!in_array($nombre, [5, 10, 15], true)) {
            $nombre = 10; // sécurité : valeur par défaut si non valide
        }

        // Calcul du temps total accordé pour la tentative
        $tempsParQuestion = 30;
        $tempsAlloue = $nombre * $tempsParQuestion;

        // Tirage aléatoire d'un ensemble de questions pour ce quiz
        $idsQuestions = $repQuestion->tirerAleatoireIdsParQuiz($idQuiz, $nombre);

        // Si le quiz ne contient pas assez de questions, on retourne à la liste
        if (count($idsQuestions) === 0) {
            $this->addFlash('warning', 'Aucune question disponible pour ce quiz.');
            return $this->redirectToRoute('app_quiz_liste', ['niveau' => $quiz->getNiveau()->getId()]);
        }

        // Récupère l'utilisateur connecté
        /** @var \App\Entity\Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        // Création de la tentative en base de données
        $tentative = $repTentative->saveTentative($quiz, $utilisateur, $nombre, $tempsAlloue);

        // Stocke en session les IDs des questions choisies pour cette tentative
        // Chaque tentative a sa propre clé de session
        $session = $req->getSession();
        $session->set('quiz_question_ids' . $tentative->getId(), $idsQuestions);

        // Redirection vers la première question
        return $this->redirectToRoute('app_quiz_jouer', [
            'id' => $tentative->getId(),
            'page' => 1
        ]);
    }
}
