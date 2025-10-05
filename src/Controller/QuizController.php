<?php

namespace App\Controller;

use App\Form\ReponseType;
use App\Repository\NiveauRepository;
use App\Repository\QuizRepository;
use App\Repository\TentativeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class QuizController extends AbstractController
{
    // Liste paginée des quiz pour un niveau donné
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(Request $req, QuizRepository $rep, NiveauRepository $repNiveau, PaginatorInterface $paginator): Response 
    {
        $idNiveau = $req->get('niveau');
        
        // Requête Doctrine (QueryBuilder) pour récupérer les quiz du niveau
        $query = $rep->createQueryBuilder('q')
        ->where('q.niveau = :niveau')
        ->setParameter('niveau', $idNiveau)
        ->getQuery();
        
        // Pagination des résultats (5 éléments par page)
        $pagination = $paginator->paginate(
            $query,
            $req->query->getInt('page', 1),
            5
        );
    
        // Rendu du template avec la pagination et l'entité Niveau (pour l'affichage)
        return $this->render('quiz/liste.html.twig', [
            'quizzes' => $pagination,
            'niveau' => $repNiveau->find($idNiveau), 
        ]);
    }

    // Crée une tentative pour un quiz et redirige vers l'écran de jeu (question par question)
    #[Route('/quiz/{id}/start', name: 'app_start_quiz', requirements: ['id' => '\d+'])]
    public function startQuiz(Request $req, TentativeRepository $rep, QuizRepository $repQuiz): Response
    {
        $idQuiz = $req->get('id');
        $quiz = $repQuiz->find($idQuiz);

        // Création/enregistrement d'une nouvelle tentative associée au quiz
        $tentative = $rep->saveTentative($quiz);

        // Redirection vers la route qui affiche les questions (paginées)
        return $this->redirectToRoute('app_quiz_jouer',['id' => $tentative->getId()]);
    }

    // Affiche l'exécution du quiz : 1 question par page, avec formulaire de réponse
    #[Route('/quiz/jouer/{id}', name: 'app_quiz_jouer')]
    public function executerQuestion(Request $req, TentativeRepository $rep, PaginatorInterface $paginator): Response
    {
        $idTentative = $req->get('id');

        // Récupère la tentative en cours et les questions du quiz
        $tentative = $rep->find($idTentative);
        $questions = $tentative->getQuiz()->getQuestions();

        // Numéro de page courante (?page=...), par défaut 1
        $page = $req->query->getInt('page', 1);

        // Pagination sur la collection de questions (1 question par page)
        $pagination = $paginator->paginate(
            $questions,
            $page,
            1
        );

        // Index de la question dans le tableau (0-based)
        $indexQuestion = $page - 1;

        // Récupération de l'objet Question correspondant à la page courante
        $questionCourante = $questions[$indexQuestion];

        // Indique si on se trouve sur la dernière page (dernière question)
        $estDernierrePage = $indexQuestion == count($questions) - 1 ?? false;

        // Création du formulaire de réponse pour la question courante
        $formReponse = $this->createForm(ReponseType::class, null, ['question' => $questionCourante]);
        $formReponse->handleRequest($req);

        // Traitement du POST : enregistre la réponse choisie dans la session
        if ($formReponse->isSubmitted() && $formReponse->isValid()) {
            $reponseChoisie = $formReponse->get('reponse')->getData();

            // Lecture/écriture des réponses en session, clés isolées par tentative
            $session = $req->getSession();
            $reponses = $session->get('quiz_reponses' . $idTentative, []);
            $reponses[$indexQuestion] = [
                'id_question' => $questionCourante->getId(),      
                'id_reponse_choisie' => $reponseChoisie->getId(), 
                'est_correcte' => $reponseChoisie->isEstCorrecte(),
            ];
            $session->set('quiz_reponses' . $idTentative, $reponses);
        }

        // Variables passées au template d'exécution
        $vars = [
            'questions' => $pagination,        
            'formReponse' => $formReponse,     
            'questionNumber' => $page,         
            'estDernierrePage' => $estDernierrePage, 
            'tentative' => $tentative          
        ];

        return $this->render ("quiz/quiz_executer_question.html.twig", $vars);
    }

    // Page de fin de quiz : calcul des résultats et affichage du pourcentage
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
