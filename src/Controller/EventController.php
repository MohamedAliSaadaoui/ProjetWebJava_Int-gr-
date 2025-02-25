<?php

namespace App\Controller;
use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EventRepository;
use App\Repository\ParticipeRepository;

class EventController extends AbstractController
{
    #[Route('/event.html', name: 'app_event')]
    public function index(): Response
    {
        return $this->render('event/Evenement.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    #[Route('/event/create', name: 'event_create')]
public function create(Request $request, EntityManagerInterface $entityManager): Response
{
   
    $event = new Event();

    // Créer le formulaire en utilisant la classe EventType
    $form = $this->createForm(EventType::class, $event);

    
    $form->handleRequest($request);

    
    if ($form->isSubmitted() && $form->isValid()) {
       
        $entityManager->persist($event);
        $entityManager->flush();

        // Ajouter un message flash pour notifier l'utilisateur
        $this->addFlash('success', 'Event created successfully!');

        // Rediriger vers une page de succès ou liste des événements
        return $this->redirectToRoute('event_list'); 
    }

    
    return $this->render('event/event_create.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/event/list', name: 'event_list')]
    public function list(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        return $this->render('event/event_list.html.twig', [
            'events' => $events,
        ]);
    }
    // #[IsGranted('ROLE_ADMIN')]
    #[Route('/event/delete/{id}', name: 'event_delete', methods: ['POST'])]
    public function delete(
        Event $event, 
        EntityManagerInterface $entityManager, 
        Request $request, 
        ParticipeRepository $participeRepository 
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            // Supprimer toutes les participations (instances de Participe) associées à l'événement
            $participations = $participeRepository->findBy(['id_event' => $event]); // Utilisation de 'id_event' au lieu de 'event'
            foreach ($participations as $participation) {
                $entityManager->remove($participation);
            }
    
            // Supprimer l'événement après les participations
            $entityManager->remove($event);
            $entityManager->flush();
    
            $this->addFlash('success', 'Event deleted successfully.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }
    
        return $this->redirectToRoute('event_list');
    }
    
// #[IsGranted('ROLE_ADMIN')]
#[Route('/event/edit/{id}', name: 'event_edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour l'événement en base
            $entityManager->flush();

            $this->addFlash('success', 'Event updated successfully.');

            return $this->redirectToRoute('event_list'); // Redirection vers la liste des événements
        }

        return $this->render('event/event_edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
