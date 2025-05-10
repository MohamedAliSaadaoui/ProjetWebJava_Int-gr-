<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\UserType;
use App\Enum\RoleEnum;
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
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use App\Form\NewPasswordType;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



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
public function newUser(
    Request $request,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager,
    MailerInterface $mailer
): Response {
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            try {
                // Récupérer et vérifier le mot de passe
                $plainPassword = $form->get('password')->getData();

                if (!$plainPassword) {
                    $this->addFlash('error', 'Le mot de passe est requis.');
                    return $this->redirectToRoute('app_user_new');
                }

                // Hacher le mot de passe
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

                // Gérer la photo de profil (VichUploader)
                $photoFile = $form->get('photoFile')->getData();
                if ($photoFile) {
                    $user->setPhotoFile($photoFile);
                }

                // Définir un rôle par défaut si aucun n’est défini
                if (!$user->getRole()) {
                    $user->setRole(RoleEnum::USER);
                }

                // Persister l'utilisateur
                $entityManager->persist($user);
                $entityManager->flush();

                // Envoyer l'email de bienvenue
                $emailMessage = (new Email())
                    ->from('noreply@yourdomain.com')
                    ->to($user->getEmail())
                    ->subject('Bienvenue sur notre plateforme')
                    ->html('<p>Bonjour ' . $user->getUsername() . ',<br>Bienvenue sur notre plateforme !</p>');

                $mailer->send($emailMessage);

                $this->addFlash('success', 'Votre compte a été créé avec succès.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                // Afficher l'erreur exacte (utile en dev)
                $this->addFlash('error', 'Erreur lors de la création du compte : ' . $e->getMessage());
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
public function delete(
    int $id,
    EntityManagerInterface $entityManager,
    Request $request,
    TokenStorageInterface $tokenStorage,
    SessionInterface $session
): Response {
    $user = $entityManager->getRepository(User::class)->find($id);

    if (!$user) {
        $this->addFlash('error', 'Utilisateur introuvable.');
        return $this->redirectToRoute('app_profile');
    }

    if ($this->getUser() !== $user) {
        $this->addFlash('error', 'Action non autorisée.');
        return $this->redirectToRoute('app_profile');
    }

    if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {

        // 🔐 Déconnexion propre AVANT suppression
        $tokenStorage->setToken(null);
        $session->invalidate();

        // 🗑 Suppression de l’utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        return $this->redirectToRoute('app_login');
    }

    $this->addFlash('error', 'Token CSRF invalide.');
    return $this->redirectToRoute('app_profile');
}


    
    #[Route('/reset-password', name: 'app_reset_password')]
    public function requestResetPassword(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            
            if (!$user) {
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cet e-mail.');
                return $this->redirectToRoute('app_reset_password');
            }
            
            // Générer un token unique avec TokenGenerator de Symfony
            $tokenGenerator = new UriSafeTokenGenerator();
            $token = $tokenGenerator->generateToken();
            
            $user->setResetPasswordToken($token);
            $user->setResetPasswordTokenExpiresAt(new \DateTime('+1 hour'));
            
            $entityManager->flush();
            
            // Générer le lien (URL absolue)
            $link = $this->generateUrl('app_new_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            
            // MODE DE DÉBOGAGE - Affiche le lien directement au lieu d'envoyer un email
            if ($_ENV['APP_ENV'] === 'dev') {
                // Toujours envoyer l'email pour le flux normal (optionnel en dev)
                try {
                    $emailMessage = (new Email())
                        ->from('no-reply@tonsite.com')
                        ->to($user->getEmail())
                        ->subject('Réinitialisation du mot de passe')
                        ->html('<p>Cliquez sur le lien pour réinitialiser votre mot de passe :</p>
                                <p><a href="' . $link . '">Réinitialiser mon mot de passe</a></p>');
                    
                    $mailer->send($emailMessage);
                } catch (\Exception $e) {
                    // Ignorer les erreurs d'envoi d'email en mode dev
                }
                
                // Afficher le lien de réinitialisation directement
                return $this->render('user/reset_link_debug.html.twig', [
                    'link' => $link,
                    'email' => $user->getEmail()
                ]);
            }
            
            // MODE PRODUCTION - Comportement normal
            $emailMessage = (new Email())
                ->from('no-reply@tonsite.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation du mot de passe')
                ->html('<p>Cliquez sur le lien pour réinitialiser votre mot de passe :</p>
                        <p><a href="' . $link . '">Réinitialiser mon mot de passe</a></p>');
            
            $mailer->send($emailMessage);
            
            $this->addFlash('success', 'Un email de réinitialisation a été envoyé.');
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('user/reset_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

#[Route('/reset-password/{token}', name: 'app_new_password')]
public function newPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, string $token): Response
{
    $user = $entityManager->getRepository(User::class)->findOneBy(['resetPasswordToken' => $token]);
    
    // Vérification du token
    if (!$user || $user->getResetPasswordTokenExpiresAt() < new \DateTime()) {
        $this->addFlash('error', 'Le lien de réinitialisation est invalide ou expiré.');
        return $this->redirectToRoute('app_reset_password');
    }
    
    $form = $this->createForm(NewPasswordType::class);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $newPassword = $form->get('password')->getData();
        
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        
        // Supprimer le token après usage
        $user->setResetPasswordToken(null);
        $user->setResetPasswordTokenExpiresAt(null);
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
        return $this->redirectToRoute('app_login');
    }
    
    return $this->render('user/reset_password_confirm.html.twig', [
        'form' => $form->createView()
    ]);
}


    #[Route('/profile', name: 'app_profile')]
    public function profile(Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }



    #[Route('/connect/google', name: 'connect_google')]
public function connectGoogle(ClientRegistry $clientRegistry): Response
{
    // Rediriger vers Google pour l'authentification
    return $clientRegistry
        ->getClient('google')
        ->redirect([
            'profile', 
            'email'
        ], []);  // Ajouter un second argument (options)
}

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheck(): Response
    {
        // Cette méthode ne sera jamais exécutée,
        // car le firewall intercepte la route
        return $this->redirectToRoute('app_profile');
    }
}
