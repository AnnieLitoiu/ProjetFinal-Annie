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
use Doctrine\ORM\EntityManagerInterface;

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
        // On charge la tentative avec le quiz associé
        $tentative = $rep->trouverAvecQuiz($idTentative);

        // Numéro de la question (page) dans l'URL, par défaut 1
        $page = $req->query->getInt('page', 1);

        $session = $req->getSession();
        // Tableau des IDs de questions tirées au sort pour cette tentative
        $idsSelection = $session->get('quiz_question_ids' . $idTentative, []);

        if (!empty($idsSelection)) {
            // Cas où on a une sélection précise de questions (5/10/15)
            $nbQuestions = count($idsSelection);

            // On s'assure que la page est dans les bornes [1 ; nbQuestions]
            if ($page < 1) {
                $page = 1;
            }
            if ($page > $nbQuestions) {
                // Si la page dépasse, on considère que le quiz est terminé
                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }

            // Requête pour charger seulement les questions sélectionnées, dans le bon ordre
            $requeteQuestions = $questionRepo->requeteParIdsAvecOrdre($idsSelection);
        } else {
            // Cas "fallback" : on prend toutes les questions du quiz
            $nbQuestions = $questionRepo->compterParQuizId($tentative->getQuiz()->getId());
            if ($nbQuestions === 0) {
                // Si le quiz n'a pas de questions, on va directement à l'écran de fin
                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }

            if ($page < 1) {
                $page = 1;
            }
            if ($page > $nbQuestions) {
                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }

            // Requête pour toutes les questions du quiz
            $requeteQuestions = $questionRepo->requeteParQuizId($tentative->getQuiz()->getId());
        }

        // On utilise le paginator pour afficher une seule question par "page"
        $pagination = $paginator->paginate($requeteQuestions, $page, 1);

        // Index de la question dans la série (0 = première question)
        $indexQuestion = $page - 1;

        // Question courante (celle affichée à l'utilisateur)
        $items = $pagination->getItems();
        $questionCourante = $items[0] ?? null;

        // Indique si on se trouve sur la dernière question du quiz
        $estDernierrePage = ($indexQuestion === $nbQuestions - 1);

        // Formulaire de réponse pour la question courante
        $formReponse = $this->createForm(ReponseType::class, null, [
            'question' => $questionCourante,
        ]);
        $formReponse->handleRequest($req);

        if ($formReponse->isSubmitted() && $formReponse->isValid()) {
            $reponseChoisie = $formReponse->get('reponse')->getData();

            // Vérification de sécurité au cas où aucune réponse n'est choisie
            if ($reponseChoisie === null) {
                $this->addFlash('warning', 'Merci de choisir une réponse avant de valider.');
            } else {
                // Lecture / mise à jour des réponses de l'utilisateur dans la session
                $session = $req->getSession();
                $cleSession = 'quiz_reponses' . $idTentative;
                $reponses = $session->get($cleSession, []);

                // On stocke la réponse à cette question (indexée par la position dans le quiz)
                $reponses[$indexQuestion] = [
                    'id_question'        => $questionCourante->getId(),
                    'id_reponse_choisie' => $reponseChoisie->getId(),
                    'est_correcte'       => $reponseChoisie->isEstCorrecte(),
                ];

                $session->set($cleSession, $reponses);

                // On passe à la question suivante ou on termine le quiz
                if (!$estDernierrePage) {
                    return $this->redirectToRoute('app_quiz_jouer', [
                        'id'   => $idTentative,
                        'page' => $page + 1,
                    ]);
                }

                // Dernière question : on redirige vers la page de résultats
                return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
            }
        }

        // Calcul du time de fin si un temps est alloué à la tentative
        $finTimestamp = null;
        if ($tentative->getTempsAlloueSecondes()) {
            $finTimestamp = $tentative->getDateDebut()->getTimestamp()
                + (int) $tentative->getTempsAlloueSecondes();
        }

        // Variables envoyées au template qui affiche la question
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
        QuestionRepository $questionRepo,
        EntityManagerInterface $entityManager
    ): Response {
        $idTentative = $req->get('id');

        // On récupère la tentative et les données stockées en session
        $tentative = $rep->trouverAvecQuiz($idTentative);
        $session   = $req->getSession();
        $reponses  = $session->get('quiz_reponses' . $idTentative, []);
        $idsSelection = $session->get('quiz_question_ids' . $idTentative, []);

        // Cas où l'utilisateur rafraîchit la page de résultats
        // La tentative est déjà terminée et la session ne contient plus de réponses
        if ($tentative->getDateFin() !== null && empty($reponses)) {
            // On relit les réponses et questions depuis la base
            $reponses = $tentative->getReponsesUtilisateur() ?? [];
            $questionIds = $tentative->getQuestionIds() ?? [];

            if (!empty($questionIds)) {
                $questions = $questionRepo->findBy(['id' => $questionIds]);
                $totalQuestionsQuiz = count($questionIds);
            } else {
                // Fallback si, pour une raison quelconque, les IDs ne sont pas stockés
                $questions = $questionRepo
                    ->requeteParQuizId($tentative->getQuiz()->getId())
                    ->getResult();
                $totalQuestionsQuiz = count($questions);
            }

            // Construction de la liste détaillée des réponses pour l'affichage
            $details     = [];
            $mapReponses = [];
            foreach ($reponses as $r) {
                $mapReponses[$r['id_question']] = $r;
            }

            $numero = 1;
            foreach ($questions as $q) {
                $qid  = $q->getId();
                $user = $mapReponses[$qid] ?? null;

                // On cherche la bonne réponse parmi les réponses possibles
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

            // Durée de la tentative (calculée à partir des dates début/fin)
            $duration = $tentative->formatDuration();

            // On réutilise les valeurs déjà stockées sur la tentative
            return $this->render('quiz/quiz_resultat.html.twig', [
                'tentative'             => $tentative,
                'reponses_correctes'    => $tentative->getReponsesCorrectes(),
                'reponses_mauvaises'    => $tentative->getReponsesMauvaises(),
                'total_questions'       => $totalQuestionsQuiz,
                'pourcentage'           => $tentative->getPourcentage(),
                'reponses_donnees'      => $tentative->getReponsesDonnees(),
                'non_repondues'         => $tentative->getNonRepondues(),
                'reponses_details'      => $details,
                'temps_ecoule_label'    => $duration['label'],
                'temps_ecoule_secondes' => $duration['seconds'],
            ]);
        }

        // Cas normal : on arrive ici juste après avoir répondu à la dernière question

        // On détermine les IDs des questions utilisées pour ce quiz
        $questionIds = !empty($idsSelection) ? $idsSelection : ($tentative->getQuestionIds() ?? []);

        if (!empty($questionIds)) {
            $questions = $questionRepo->findBy(['id' => $questionIds]);
            // Le total correspond au nombre de questions tirées (5/10/15)
            $totalQuestionsQuiz = count($questionIds);
        } else {
            $questions = $questionRepo
                ->requeteParQuizId($tentative->getQuiz()->getId())
                ->getResult();
            $totalQuestionsQuiz = count($questions);
        }

        // Calcul des statistiques globales de la tentative
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

        // On sauvegarde sur la tentative les IDs des questions jouées
        $questionIds = !empty($questionIds) ? $questionIds : $idsSelection;
        $tentative->setQuestionIds($questionIds);

        // Appel au repository pour mettre à jour la tentative en base (score, stats, etc.)
        $rep->finirTentative(
            $reponsesCorrectes,
            $reponsesMauvaises,
            $reponsesDonnees,
            $nonRepondues,
            $pourcentage,
            $reponses,
            $tentative
        );
        $entityManager->persist($tentative);
        $entityManager->flush();

        // Construction du détail des réponses pour l'affichage
        $details     = [];
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

        // On nettoie la session pour cette tentative une fois qu'on a tout utilisé
        $session->remove('quiz_reponses' . $idTentative);
        $session->remove('quiz_question_ids' . $idTentative);

        $duration = $tentative->formatDuration();

        return $this->render('quiz/quiz_resultat.html.twig', [
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
        ]);
    }
}
