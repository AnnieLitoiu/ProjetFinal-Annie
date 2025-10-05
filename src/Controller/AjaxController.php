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
    // Route d'inscription : affiche le formulaire et traite sa soumission (GET/POST)
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,                               
        UserPasswordHasherInterface $userPasswordHasher, 
        Security $security,                               
        EntityManagerInterface $entityManager             
    ): Response {
        // 1) Crée une nouvelle entité Utilisateur vide (qui sera remplie par le formulaire)
        $user = new Utilisateur();

        // 2) Construit le formulaire lié à l'entité : champs = définis dans RegistrationFormType
        //    -> Les setters de l'entité seront appelés lors du handleRequest si les champs sont valides
        $form = $this->createForm(RegistrationFormType::class, $user);

        // 3) Lie la requête au formulaire : hydrate $user avec les données POST si soumission
        $form->handleRequest($request);

        // 4) Si le formulaire a été soumis ET validé par les contraintes (FormType + Validator)
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            // Récupère le mot de passe en clair depuis le champ "plainPassword" (non mappé à l'entité)
            $plainPassword = $form->get('plainPassword')->getData();

            // 5) Hache le mot de passe en clair puis le stocke sur l'entité (jamais conserver le clair)
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // 6) Attribue le rôle par défaut (peut être enrichi plus tard : validation e-mail, etc.)
            $user->setRoles(['ROLE_USER']);

            // 7) Enregistre l'utilisateur en base : persist (marque pour insertion) puis flush (écrit)
            $entityManager->persist($user);
            $entityManager->flush();

            return $security->login($user, 'form_login', 'main');
        }
        
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
