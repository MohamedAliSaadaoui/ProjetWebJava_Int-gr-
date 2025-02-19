<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierControlerController extends AbstractController
{
    #[Route('/panier', name: 'app_panier_controler')]
    public function index(): Response
    {
        return $this->render('panier_controler/panier.html.twig', [
            'controller_name' => 'PanierControlerController',
        ]);
    }
}
