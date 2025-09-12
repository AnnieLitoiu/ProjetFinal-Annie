<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EffaceMoiController extends AbstractController
{
    #[Route('/efface/moi', name: 'app_efface_moi')]
    public function index(): Response
    {
        return $this->render('efface_moi/index.html.twig', [
            'controller_name' => 'EffaceMoiController',
        ]);
    }
}
