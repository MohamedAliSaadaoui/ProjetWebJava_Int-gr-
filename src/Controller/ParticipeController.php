<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EventRepository;
use App\Form\ParticipeType;
use App\Repository\ParticipeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Event;
use App\Entity\Participe;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;


class ParticipeController extends AbstractController
{
    #[Route('/participe', name: 'app_participe')]
    public function index(): Response
    {
        return $this->render('participe/index.html.twig', [
            'controller_name' => 'ParticipeController',
        ]);
    }
    #[Route('/participe/create/{id}', name: 'participation_create')]
    public function new(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour participer à un événement.');
            return $this->redirectToRoute('app_login'); // Ajustez selon votre route de login
        }
        
        $event = $entityManager->getRepository(Event::class)->find($id);
    
        if (!$event) {
            throw $this->createNotFoundException('Event not found.');
        }
    
        // Vérifier si l'utilisateur participe déjà à cet événement
        $existingParticipation = $entityManager->getRepository(Participe::class)
            ->findOneBy([
                'id_event' => $event,
                'user' => $user
            ]);
    
        if ($existingParticipation) {
            $this->addFlash('error', 'Vous participez déjà à cet événement.');
            return $this->redirectToRoute('event_list');
        }
    
        $participation = new Participe();
        $participation->setIdEvent($event); // Pré-remplit l'événement
        $participation->setUser($user);   // Définir l'utilisateur connecté
    
        // Initialiser le nombre de places à 1 pour une nouvelle participation
        $participation->setNbrPlace(1);
    
        // Créer le formulaire
        $form = $this->createForm(ParticipeType::class, $participation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participation);
            $entityManager->flush();
    
            // Message flash
            $this->addFlash('success', 'You have successfully registered for the event.');
    
            return $this->redirectToRoute('event_list'); // Redirection après succès
        }
    
        return $this->render('participe/participe_create.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
    #[Route('/participe/list', name: 'participation_list')]
    public function list(EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        // Récupérer toutes les participations avec leurs événements associés
        $participations = $entityManager->getRepository(Participe::class)->findBy(['user' => $user]);
    
        return $this->render('participe/participe_list.html.twig', [
            'participations' => $participations,
        ]);
    }
    
#[Route('/participe/{id}/delete', name: 'participation_delete', methods: ['POST'])]
public function delete(int $id, EntityManagerInterface $entityManager): Response
{
    // Trouver la participation à supprimer
    $participation = $entityManager->getRepository(Participe::class)->find($id);

    if (!$participation) {
        // Si la participation n'existe pas, rediriger avec un message d'erreur
        $this->addFlash('error', 'Participation not found.');
        return $this->redirectToRoute('participation_list');
    }

    // Supprimer la participation
    $entityManager->remove($participation);
    $entityManager->flush();

    // Message de succès
    $this->addFlash('success', 'Participation deleted successfully.');

    return $this->redirectToRoute('participation_list');
}
#[Route('/participe/{id?0}/edit', name: 'participation_edit')]
public function edit(int $id = null, Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();
    
    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour modifier une participation.');
        return $this->redirectToRoute('app_login');
    }
    
    // Si un ID est fourni, on cherche la participation correspondante
    $participation = $id ? $entityManager->getRepository(Participe::class)->find($id) : new Participe();

    // Si la participation n'existe pas, créer une nouvelle
    if (!$participation && $id) {
        $this->addFlash('error', 'Participation not found.');
        return $this->redirectToRoute('participation_list');
    }

    // Pour une nouvelle participation, définir l'utilisateur
    if (!$id) {
        $participation->setIdUser($user);
    } else {
        // Vérifier que l'utilisateur est autorisé à modifier cette participation
        if ($participation->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette participation.');
            return $this->redirectToRoute('participation_list');
        }
    }

    // Créer ou réutiliser le formulaire pour la participation
    $form = $this->createForm(ParticipeType::class, $participation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Sauvegarder les modifications ou la nouvelle participation
        $entityManager->persist($participation);
        $entityManager->flush();

        // Message de succès
        $this->addFlash('success', $id ? 'Participation updated successfully.' : 'Participation created successfully.');

        // Rediriger vers la liste des participations
        return $this->redirectToRoute('participation_list');
    }

    // Afficher le formulaire de création ou de modification
    return $this->render('participe/participe_edit.html.twig', [
        'form' => $form->createView(),
        'participation' => $participation,
    ]);
}

}



