<?php

namespace App\Controller;

use App\Form\ReponseType;
use App\Repository\TentativeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\QuestionRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class QuestionsController extends AbstractController
{
    #[Route('/quiz/jouer/{id}', name: 'app_quiz_jouer')]
    public function executerQuestion(
        Request $req,
        TentativeRepository $rep,
        QuestionRepository $questionRepo,
        PaginatorInterface $paginator
    ): Response {
        $idTentative = $req->get('id');
        $tentative = $rep->trouverAvecQuiz($idTentative);

        $page = $req->query->getInt('page', 1);

        $session = $req->getSession();
        $idsSelection = $session->get('quiz_question_ids' . $idTentative, []);

        if (!empty($idsSelection)) {
            $nbQuestions = count($idsSelection);

            if ($page < 1) {
                $page = 1;
            }
            if ($page > $nbQuestions) {
                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }

            $requeteQuestions = $questionRepo->requeteParIdsAvecOrdre($idsSelection);
        } else {
            $nbQuestions = $questionRepo->compterParQuizId($tentative->getQuiz()->getId());
            if ($nbQuestions === 0) {
                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }

            if ($page < 1) {
                $page = 1;
            }
            if ($page > $nbQuestions) {
                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }

            $requeteQuestions = $questionRepo->requeteParQuizId($tentative->getQuiz()->getId());
        }

        $pagination = $paginator->paginate($requeteQuestions, $page, 1);

        // Index de la question dans le tableau (0-based)
        $indexQuestion = $page - 1;

        // Récupération de l'objet Question correspondant à la page courante
        $items = $pagination->getItems();
        $questionCourante = $items[0] ?? null;

        // Indique si on se trouve sur la dernière page (dernière question)
        $estDernierrePage = ($indexQuestion === $nbQuestions - 1);

        // Formulaire de réponse pour la question courante
        $formReponse = $this->createForm(ReponseType::class, null, [
            'question' => $questionCourante,
        ]);
        $formReponse->handleRequest($req);

        if ($formReponse->isSubmitted() && $formReponse->isValid()) {
            $reponseChoisie = $formReponse->get('reponse')->getData();

            // Sécurité : normalement ce n'est jamais null (required=true)
            if ($reponseChoisie === null) {
                $this->addFlash('warning', 'Merci de choisir une réponse avant de valider.');
            } else {
                // Lecture / mise à jour des réponses en session
                $session = $req->getSession();
                $cleSession = 'quiz_reponses' . $idTentative;
                $reponses = $session->get($cleSession, []);

                $reponses[$indexQuestion] = [
                    'id_question'        => $questionCourante->getId(),
                    'id_reponse_choisie' => $reponseChoisie->getId(),
                    'est_correcte'       => $reponseChoisie->isEstCorrecte(),
                ];

                $session->set($cleSession, $reponses);

                // Page suivante ou fin du quiz
                if (!$estDernierrePage) {
                    return $this->redirectToRoute('app_quiz_jouer', [
                        'id'   => $idTentative,
                        'page' => $page + 1,
                    ]);
                }

                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }
        }

        $finTimestamp = null;
        if ($tentative->getTempsAlloueSecondes()) {
            $finTimestamp = $tentative->getDateDebut()->getTimestamp() + (int) $tentative->getTempsAlloueSecondes();
        }

        $vars = [
            'questions'        => $pagination,
            'formReponse'      => $formReponse,
            'questionNumber'   => $page,
            'totalQuestions'   => $nbQuestions,
            'estDernierrePage' => $estDernierrePage,
            'tentative'        => $tentative,
            'finTimestamp'     => $finTimestamp,
        ];

        return $this->render('quiz/quiz_executer_question.html.twig', $vars);
    }

    #[Route('/quiz/terminer/{id}', name: 'app_quiz_terminer')]
    public function terminerQuiz(
        Request $req,
        TentativeRepository $rep,
        QuestionRepository $questionRepo
    ): Response {
        $idTentative = $req->get('id');

        // Récupérer la tentative + données de session
        $tentative = $rep->trouverAvecQuiz($idTentative);
        $session = $req->getSession();
        $reponses = $session->get('quiz_reponses' . $idTentative, []);
        $idsSelection = $session->get('quiz_question_ids' . $idTentative, []);

        // Total de questions (5 / 10 / 15 ou toutes les questions)
        $choisi = $tentative->getNombreQuestions();
        $totalQuestionsQuiz = max(
            1,
            ($choisi ?? $questionRepo->compterParQuizId($tentative->getQuiz()->getId()))
        );

        // Statistiques
        $reponsesDonnees   = count($reponses);
        $reponsesCorrectes = 0;

        foreach ($reponses as $reponse) {
            if (!empty($reponse['est_correcte'])) {
                $reponsesCorrectes++;
            }
        }

        $diviseur    = max(1, $totalQuestionsQuiz);
        $pourcentage = round(($reponsesCorrectes / $diviseur) * 100, 2);

        $reponsesMauvaises = max(0, $reponsesDonnees - $reponsesCorrectes);
        $nonRepondues      = max(0, $totalQuestionsQuiz - $reponsesDonnees);

        // Sauvegarde de la tentative
        $rep->finirTentative(
            $reponsesCorrectes,
            $reponsesMauvaises,
            $reponsesDonnees,
            $nonRepondues,
            $pourcentage,
            $reponses,
            $tentative
        );

        // Construction des détails des réponses AVANT de nettoyer la session
        $details = [];

        if (!empty($idsSelection)) {
            // On récupère uniquement les questions tirées au sort pour ce quiz
            $questions = $questionRepo->findBy([
                'id' => $idsSelection,
            ]);
        } else {
            // Fallback : toutes les questions du quiz (cas ancien ou sans sélection)
            $questions = $questionRepo
                ->requeteParQuizId($tentative->getQuiz()->getId())
                ->getResult();
}


        $mapReponses = [];
        foreach ($reponses as $r) {
            $mapReponses[$r['id_question']] = $r;
        }

        $numero = 1;
        foreach ($questions as $q) {
            $qid  = $q->getId();
            $user = $mapReponses[$qid] ?? null;

            $bonneRep = null;
            foreach ($q->getReponses() as $repQ) {
                if ($repQ->isEstCorrecte()) {
                    $bonneRep = $repQ;
                    break;
                }
            }

            $details[] = [
                'numero'        => $numero++,
                'intitule'      => $q->getEnonce(),
                'votre_reponse' => $user && !empty($user['id_reponse_choisie'])
                    ? $q->getReponses()
                        ->filter(fn($r) => $r->getId() === $user['id_reponse_choisie'])
                        ->first()?->getTexte()
                    : null,
                'bonne_reponse' => $bonneRep?->getTexte(),
                'est_correcte'  => $user['est_correcte'] ?? false,
            ];
        }

        // Nettoyage de la session une fois qu'on a tout utilisé
        $session->remove('quiz_reponses' . $idTentative);
        $session->remove('quiz_question_ids' . $idTentative);

        $duration = $tentative->formatDuration();

        $vars = [
            'tentative'             => $tentative,
            'reponses_correctes'    => $reponsesCorrectes,
            'reponses_mauvaises'    => $reponsesMauvaises,
            'total_questions'       => $totalQuestionsQuiz,
            'pourcentage'           => $pourcentage,
            'reponses_donnees'      => $reponsesDonnees,
            'non_repondues'         => $nonRepondues,
            'reponses_details'      => $details,
            'temps_ecoule_label'    => $duration['label'],
            'temps_ecoule_secondes' => $duration['seconds'],
        ];

        return $this->render('quiz/quiz_resultat.html.twig', $vars);
    }
}
