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

            // Définit le rôle par défaut
            $user->setRoles(['ROLE_USER']);

            // Sauvegarde l'utilisateur en base
            $entityManager->persist($user);
            $entityManager->flush();

            // ✅ Ajoute un message de confirmation visible après redirection
            $prenom = $user->getPrenom() ?: 'Utilisateur';
            $this->addFlash('success', sprintf('Bienvenue %s ! Votre compte a bien été créé !', $prenom));
        
            // Connecte automatiquement l'utilisateur après inscription
            $security->login($user, 'form_login', 'main');

            // Redirige vers la page d'accueil
            return $this->redirectToRoute('accueil_welcome'); 
        }

        // Affiche le formulaire d'inscription (par défaut)
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
