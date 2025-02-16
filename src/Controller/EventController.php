<?php

namespace App\Controller;
use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(): Response
    {
        return $this->render('event/Evenement.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    #[Route('/event/create', name: 'event_create')] 
    public function create(Request $request, EntityManagerInterface $entityManager): Response
{
    // Créer un objet Event pour le formulaire
    $event = new Event();

    // Créer le formulaire en utilisant la classe EventType
    $form = $this->createForm(EventType::class, $event);

    // Traiter la soumission du formulaire
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Gérer l'image téléchargée, si présente
        $image = $form->get('image')->getData();
        if ($image) {
            $newFilename = uniqid() . '.' . $image->guessExtension();
            $image->move(
                $this->getParameter('event_images_directory'), // Répertoire de stockage des images
                $newFilename
            );
            $event->setImage($newFilename); // Lier le nom de l'image à l'événement
        }

        // Persister l'événement dans la base de données
        $entityManager->persist($event);
        $entityManager->flush();

        // Ajouter un message flash pour notifier l'utilisateur
        $this->addFlash('success', 'Event created successfully!');

        // Rediriger vers une page de succès ou liste des événements
        return $this->redirectToRoute('event_list'); // Change cela selon la route de la liste des événements
    }

    // Afficher le formulaire dans la vue
    return $this->render('event/event_create.html.twig', [
        'form' => $form->createView(),
    ]);
}
}
