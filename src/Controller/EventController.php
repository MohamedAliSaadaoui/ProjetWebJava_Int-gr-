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
use App\Service\GeocodingService;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use App\Entity\User;

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
    public function create(Request $request, EntityManagerInterface $entityManager, GeocodingService $geocodingService): Response
{
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();
    
    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        $this->addFlash('error', 'You must be logged in to create an event.');
        return $this->redirectToRoute('app_login'); // Ajustez selon votre route de login
    }
    
    // Créer une nouvelle instance de l'entité Event
    $event = new Event();
    
    // Définir l'utilisateur comme créateur de l'événement
    $event->setCreator($user);
    
    // Créer le formulaire à partir de la classe EventType
    $form = $this->createForm(EventType::class, $event);
    
    // Traiter la soumission du formulaire
    $form->handleRequest($request);
    
    // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer le lieu de l'événement
        $lieu = $event->getLieu();
    
        // Utiliser le service GeocodingService pour obtenir les coordonnées (latitude et longitude)
        $coordinates = $geocodingService->getCoordinates($lieu);
    
        // Vérifier si des coordonnées ont été trouvées
        if ($coordinates) {
            // Assigner les coordonnées à l'événement
            $event->setLatitude($coordinates['latitude']);
            $event->setLongitude($coordinates['longitude']);
        } else {
            // Si aucune coordonnée n'est trouvée, ajouter un message flash d'erreur
            $this->addFlash('error', 'Could not find coordinates for the specified location.');
            return $this->redirectToRoute('event_create');
        }
    
        // Persister l'événement dans la base de données
        $entityManager->persist($event);
        $entityManager->flush();
    
        // Ajouter un message flash pour informer l'utilisateur que l'événement a été créé
        $this->addFlash('success', 'Event created successfully!');
    
        // Rediriger vers la liste des événements (ou une autre page selon ton besoin)
        return $this->redirectToRoute('event_list');
    }
    
    // Rendre la vue du formulaire
    return $this->render('event/event_create.html.twig', [
        'form' => $form->createView(),
    ]);
}
    
#[Route('/event/list', name: 'event_list')]
public function list(EventRepository $eventRepository, Request $request): Response
{
    $page = $request->query->getInt('page', 1); // Récupère le numéro de la page depuis l'URL (par défaut 1)
    $limit = 5; // Nombre d'événements par page

    // Création de la pagination avec Pagerfanta
    $queryBuilder = $eventRepository->createQueryBuilder('e')->orderBy('e.dateFin', 'DESC');
    $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
    $pagerfanta->setMaxPerPage($limit);
    $pagerfanta->setCurrentPage($page);

    // Vérifie si l'utilisateur connecté est le créateur de l'événement
    $events = $pagerfanta->getCurrentPageResults();
    $editableEvents = [];
    foreach ($events as $event) {
        if ($event->getCreator() === $this->getUser()) {
            $editableEvents[] = $event; // Ajoute les événements créés par l'utilisateur
        }
    }

    return $this->render('event/event_list.html.twig', [
        'events' => $pagerfanta,
        'editableEvents' => $editableEvents, // Passer les événements modifiables au template
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
public function edit(Event $event, Request $request, EntityManagerInterface $entityManager, GeocodingService $geocodingService): Response
{
    $form = $this->createForm(EventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer le lieu de l'événement
        $lieu = $event->getLieu();

        // Utiliser le service de géocodage pour obtenir les nouvelles coordonnées
        $coordinates = $geocodingService->getCoordinates($lieu);

        if ($coordinates) {
            // Mettre à jour les coordonnées de l'événement
            $event->setLatitude($coordinates['latitude']);
            $event->setLongitude($coordinates['longitude']);
        } else {
            // Si aucune coordonnée trouvée, afficher un message d'erreur
            $this->addFlash('error', 'Could not find coordinates for the specified location.');
            return $this->redirectToRoute('event_edit', ['id' => $event->getId()]);
        }

        // Sauvegarde des modifications
        $entityManager->flush();

        $this->addFlash('success', 'Event updated successfully.');

        return $this->redirectToRoute('event_list');
    }

    return $this->render('event/event_edit.html.twig', [
        'form' => $form->createView(),
        'event' => $event,
    ]);
}

}
