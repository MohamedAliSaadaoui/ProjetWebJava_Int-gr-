<?php
namespace App\Controller;

use App\Service\OrderTrackingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TrackingController extends AbstractController
{
    #[Route('/track/map/{trackingNumber}', name: 'track_order_map')]
    public function trackOrderMap(OrderTrackingService $trackingService, string $trackingNumber): JsonResponse
    {
        $location = $trackingService->getTrackingLocation($trackingNumber);
        return $this->json($location);
    }

    #[Route('/tracking/{trackingNumber}', name: 'tracking_page')]
    public function trackingPage(string $trackingNumber)
    {
        return $this->render('tracking.html.twig', [
            'trackingNumber' => $trackingNumber
        ]);
    }
}
