<?php

namespace App\Controller;

use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatsController extends AbstractController{
    #[Route('/stats', name: 'app_dashboard')]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $mostReportedCategory = $reclamationRepository->getMostReportedCategory();
        $resolvedCount = $reclamationRepository->countResolved();
        $inProgressCount = $reclamationRepository->countInProgress();
        $topUser = $reclamationRepository->getTopUser();
        $peakDate = $reclamationRepository->getPeakComplaintDate();

        return $this->render('stats/index.html.twig', [
            'mostReportedCategory' => $mostReportedCategory,
            'resolvedCount' => $resolvedCount,
            'inProgressCount' => $inProgressCount,
            'topUser' => $topUser,
            'peakDate' => $peakDate,
        ]);
    }

}
