<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Form\ResetPasswordType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        return $this->render('user/index.html.twig', [
            'users' => $entityManager->getRepository(User::class)->findAll(),
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function newUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Récupérer le mot de passe en clair
                    $plainPassword = $form->get('password')->getData();

                    // Hacher le mot de passe
                    if ($plainPassword) {
                        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                    }

                    // Ajouter un rôle par défaut si aucun rôle n'est spécifié
                    if (empty($user->getRoles())) {
                        $user->setRoles(['ROLE_USER']);
                    }

                    // Sauvegarder l'utilisateur
                    $entityManager->persist($user);
                    $entityManager->flush();

                    // Envoi d'un email de bienvenue
                    $emailMessage = (new Email())
                        ->from('noreply@yourdomain.com')
                        ->to($user->getEmail())
                        ->subject('Bienvenue sur notre plateforme')
                        ->html('<p>Bonjour ' . $user->getUsername() . ',<br>Bienvenue sur notre plateforme !</p>');
                    
                    $mailer->send($emailMessage);

                    $this->addFlash('success', 'Votre compte a été créé avec succès.');
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création du compte.');
                }
            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs avant de valider.');
            }
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/edit', name: 'app_user_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
    
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_user');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_user');
    }

    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $entityManager->getRepository(User::class)->findOneByEmail($email);

            if ($user) {
                // Générer un token unique
                $token = bin2hex(random_bytes(32));
                $user->setResetPasswordToken($token);

                // Sauvegarder le token dans la base de données
                $entityManager->flush();

                // Créer le lien avec le token pour réinitialiser le mot de passe
                $resetPasswordUrl = $this->generateUrl('app_reset_password_confirm', [
                    'token' => $token
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                // Envoi de l'email
                $emailMessage = (new Email())
                    ->from('noreply@yourdomain.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html('<p>Veuillez cliquer sur ce lien pour réinitialiser votre mot de passe : <a href="' . $resetPasswordUrl . '">Réinitialiser le mot de passe</a></p>');

                $mailer->send($emailMessage);
                $this->addFlash('success', 'Un email de réinitialisation vous a été envoyé.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cet email.');
            }
        } else {
            $this->addFlash('error', 'Veuillez vérifier les champs du formulaire.');
        }

        return $this->render('user/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password_confirm')]
    public function resetPasswordConfirm(string $token, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Chercher l'utilisateur en fonction du token
        $user = $entityManager->getRepository(User::class)->findOneBy(['resetPasswordToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token de réinitialisation invalide.');
            return $this->redirectToRoute('app_login');
        }

        // Créer et traiter le formulaire de réinitialisation du mot de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();

            // Hacher le nouveau mot de passe et l'assigner à l'utilisateur
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $user->setResetPasswordToken(null); // Supprimer le token une fois utilisé

            // Sauvegarder l'utilisateur avec le nouveau mot de passe
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/reset_password_confirm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(Security $security): Response
    {
        $user = $security->getUser();

        if(!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

}
