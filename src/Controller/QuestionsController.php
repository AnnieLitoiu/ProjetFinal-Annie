<?php

namespace App\Controller;

use App\Form\ReponseType;
use App\Repository\TentativeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class QuestionsController extends AbstractController
{   
    #[Route('/quiz/jouer/{id}', name: 'app_quiz_jouer')]
    public function executerQuestion(Request $req, TentativeRepository $rep, PaginatorInterface $paginator): Response
    {

    $idTentative = $req->get('id');

    // Récupère la tentative en cours et les questions du quiz
    $tentative = $rep->find($idTentative);
    $questions = $tentative->getQuiz()->getQuestions();

    // Numéro de page courante (?page=...), par défaut 1
    $page = $req->query->getInt('page', 1);

    $nbQuestions = count($questions);
    if ($nbQuestions === 0) {

        return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
    }
    if ($page < 1) { $page = 1; }
    if ($page > $nbQuestions) {
        
        return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
    }

    // Pagination sur la collection de questions (1 question par page)
    $pagination = $paginator->paginate($questions, $page, 1);

    // Index de la question dans le tableau (0-based)
    $indexQuestion = $page - 1;

    // Récupération de l'objet Question correspondant à la page courante
    $questionCourante = $questions[$indexQuestion];

    // Indique si on se trouve sur la dernière page (dernière question)
    $estDernierrePage = ($indexQuestion === $nbQuestions - 1);

    // Création du formulaire de réponse pour la question courante
    $formReponse = $this->createForm(ReponseType::class, null, ['question' => $questionCourante]);
    $formReponse->handleRequest($req);

    // Si on a validé la réponse, on enregistre puis on redirige. PRG (Post/Redirect/Get)
    if ($formReponse->isSubmitted() && $formReponse->isValid()) {
        $reponseChoisie = $formReponse->get('reponse')->getData();

        // Lecture des réponses en session, clés isolées par tentative
        $session = $req->getSession();
        $cleSession = 'quiz_reponses' . $idTentative;
        $reponses = $session->get($cleSession, []);

        // On mémorise la réponse pour cette question (index courant)
        $reponses[$indexQuestion] = [
            'id_question'        => $questionCourante->getId(),
            'id_reponse_choisie' => $reponseChoisie->getId(),
            'est_correcte'       => $reponseChoisie->isEstCorrecte(),
        ];
        $session->set($cleSession, $reponses);

        // Redirection automatique :
        // - Si ce n'est pas la dernière question -> page suivante
        // - Sinon -> page de fin
        if (!$estDernierrePage) {
            return $this->redirectToRoute('app_quiz_jouer', [
                'id'   => $idTentative,
                'page' => $page + 1,
            ]);
        }
        return $this->redirectToRoute('app_quiz_terminer', ['id' => $idTentative]);
    }

    $vars = [
        'questions' => $pagination,
        'formReponse' => $formReponse,
        'questionNumber' => $page,
        'estDernierrePage' => $estDernierrePage,
        'tentative' => $tentative
        ];

        return $this->render("quiz/quiz_executer_question.html.twig", $vars);
    }

    #[Route('/quiz/terminer/{id}', name: 'app_quiz_terminer')]
    public function terminerQuiz(Request $req, TentativeRepository $rep): Response
    {
        $idTentative = $req->get('id');

        // Récupération de la tentative et des réponses stockées en session
        $tentative = $rep->find($idTentative);
        $session = $req->getSession();
        $reponses = $session->get('quiz_reponses' . $idTentative, []); // cherche la clé en question ('quiz_reponses' . $idTentative) dans la session. Si tu la trouve pas, envoie un tableau vide [] 

        // Comptage du total de questions (pour calcul de pourcentage)
        $totalquestions = count($tentative->getQuiz()->getQuestions());
        $totalQuestionsQuiz = 15;
        $reponsesCorrectes = 0;

        // Parcourt des réponses enregistrées pour calculer le pourcentage
        foreach($reponses as $reponse){
            if (!empty($reponse['est_correcte'])){
                $reponsesCorrectes++;
            }
        }
        // Calcul du pourcentage 
        $diviseur = max(1, $totalQuestionsQuiz);
        $pourcentage = round(($reponsesCorrectes / $diviseur) * 100, 2);

        // Nombre de réponses données / non répondues
        $reponsesDonnees = count($reponses);
        $reponsesMauvaises = max(0, $reponsesDonnees - $reponsesCorrectes);
        $nonRepondues = max(0, $totalQuestionsQuiz - $reponsesDonnees);
        $rep->finirTentative(
            $reponsesCorrectes,
            $reponsesMauvaises,
            $reponsesDonnees,
            $nonRepondues,
            $pourcentage,
            $reponses,
            $tentative
        );
        $session->remove('quiz_reponses' . $idTentative);
        $details = [];
        $questions = $tentative->getQuiz()->getQuestions();
        $mapReponses = [];
        foreach ($reponses as $r) {
            $mapReponses[$r['id_question']] = $r; 
        }

        $numero = 1;
        foreach ($questions as $q) {
            $qid = $q->getId();
            $user = $mapReponses[$qid] ?? null;

            $bonneRep = null;
            foreach ($q->getReponses() as $r) {
                if ($r->isEstCorrecte()) { $bonneRep = $r; break; }
            }

            $details[] = [
                'numero'         => $numero++,
                'intitule'       => $q->getEnonce(),                 
                'votre_reponse'  => $user ? ($q->getReponses()
                                    ->filter(fn($r) => $r->getId() === $user['id_reponse_choisie'])
                                    ->first()?->getTexte()) : null,
                'bonne_reponse'  => $bonneRep?->getTexte(),
                'est_correcte'   => $user['est_correcte'] ?? false,
                'est_skip'       => $user === null,
            ];
        }
        $duration = $tentative->formatDuration();
        $vars = [
            'tentative'           => $tentative,
            'reponses_correctes'  => $reponsesCorrectes, 
            'reponses_mauvaises'  => $reponsesMauvaises,      
            'total_questions'     => $totalquestions,             
            'pourcentage'         => $pourcentage,               
            'reponses_donnees'    => $reponsesDonnees,
            'non_repondues'       => $nonRepondues,
            'reponses_details'    => $details,
            'temps_ecoule_label'  => $duration['label'],
            'temps_ecoule_secondes' => $duration['seconds'],
        ];
    
        return $this->render ("quiz/quiz_resultat.html.twig", $vars);
    }
}
