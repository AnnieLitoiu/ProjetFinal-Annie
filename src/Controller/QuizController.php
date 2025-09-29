<?php

namespace App\Controller;

use App\Entity\Tentative;
use App\Form\TentativeType;
use App\Repository\NiveauRepository;
use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class QuizController extends AbstractController
{
    #[Route('/quiz/liste/{niveau}', name: 'app_quiz_liste')]
    public function quizListe(
        Request $req,
        QuizRepository $rep,
        NiveauRepository $repNiveau,
        PaginatorInterface $paginator
    ): Response {
        $idNiveau = $req->get('niveau');
    
        // Requête Doctrine via le repository
        $query = $rep->createQueryBuilder('q')
            ->where('q.niveau = :niveau')
            ->setParameter('niveau', $idNiveau)
            ->getQuery();
    
        // Paginer la requête
        $pagination = $paginator->paginate(
            $query,
            $req->query->getInt('page', 1), 
            5
        );
    
        return $this->render('quiz/liste.html.twig', [
            'quiz' => $pagination,
            'niveau' => $repNiveau->find($idNiveau), 
        ]);
    }

    #[Route('/quiz/{id}', name: 'app_details_quiz')]
    public function detailsQuiz(Request $req, QuizRepository $rep): Response
    {
        $idQuiz = $req->get('id');

        
        $tentative = new Tentative();
        $formTentative = $this->createForm(
            TentativeType::class,
            $tentative,
            array(
                'action' => $this->generateURL ("quiz_tentative_commencer"),
                'method' => 'POST',
                'quiz_id' => $idQuiz
            )
        );
        $vars = ['quiz' => $rep->find($idQuiz), 'formTentative' => $formTentative->createView()];

        return $this->render('quiz/details-quiz.html.twig', $vars);
    }

    #[Route('/tentative/commencer', name: 'quiz_tentative_commencer')]
    public function insertTentative(): Response
    {
        $vars = [];
        return $this->render('quiz/tentative-commencer.html.twig', $vars);
    }                                                                                                                                       
}
