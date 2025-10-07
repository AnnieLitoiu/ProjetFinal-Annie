<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher, 
        Security $security,                               
        EntityManagerInterface $entityManager             
    ): Response {
        $user = new Utilisateur();

        // Construit le formulaire lié à l'entité Utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Lie la requête (GET/POST) aux données du formulaire
        $form->handleRequest($request);

        // Si le formulaire est soumis ET valide, on traite l'inscription
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData(); // récupère le mot de passe en clair

            // Hache le mot de passe en clair et l'enregistre sur l'entité
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setRoles(['ROLE_USER']);

            // Sauvegarde l'utilisateur en base
            $entityManager->persist($user);
            $entityManager->flush();
        
            // Connecte l'utilisateur et redirige vers la page d'accueil
            $security->login($user, 'form_login', 'main');
            return $this->redirectToRoute('accueil_welcome'); 
        }

        // Affiche le formulaire d'inscription -> plus besoin pour moment
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
