<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipeController extends AbstractController
{
    #[Route('/participe', name: 'app_participe')]
    public function index(): Response
    {
        return $this->render('participe/index.html.twig', [
            'controller_name' => 'ParticipeController',
        ]);
    }
}
