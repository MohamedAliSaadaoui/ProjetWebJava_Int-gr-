<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EventRepository;
use App\Repository\ParticipeRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user/events')]
#[IsGranted('ROLE_USER')]
class UserEventController extends AbstractController
{
    #[Route('/', name: 'app_user_events')]
    public function index(EventRepository $eventRepository, ParticipeRepository $participationRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer les événements créés par l'utilisateur
        $createdEvents = $eventRepository->findBy(['creator' => $user]);
        
        // Récupérer les événements auxquels l'utilisateur participe
        $participations = $participationRepository->findBy(['user' => $user]);
        
        // Extraire les événements des participations
        $participatingEvents = [];
        foreach ($participations as $participation) {
            $participatingEvents[] = $participation->getIdEvent();
        }
        
        return $this->render('user_event/events.html.twig', [
            'createdEvents' => $createdEvents,
            'participatingEvents' => $participatingEvents,
        ]);
    }
    
}
