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
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher, 
        Security $security,                               
        EntityManagerInterface $entityManager             
    ): Response {
        // 1) Crée un nouvel utilisateur vide
        $user = new Utilisateur();

        // 2) Construit le formulaire lié à l'entité Utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);

        // 3) Lie la requête (GET/POST) aux données du formulaire
        $form->handleRequest($request);

        // 4) Si le formulaire est soumis ET valide, on traite l'inscription
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData(); // récupère le mot de passe en clair

            // 5) Hache le mot de passe en clair et l'enregistre sur l'entité
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // 6) Attribue le rôle par défaut à l'utilisateur
            $user->setRoles(['ROLE_USER']);

            // 7) Persiste puis écrit en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            return $security->login($user, 'form_login', 'main');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
