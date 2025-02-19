<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetailProduitControlerController extends AbstractController
{
    #[Route('/detailproduitcontroler', name: 'app_detail_produit_controler')]
    public function index(): Response
    {
        return $this->render('detail_produit_controler/detailproduit.html.twig', [
            'controller_name' => 'DetailProduitControlerController',
        ]);
    }
}
