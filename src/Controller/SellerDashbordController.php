<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SellerDashbordController extends AbstractController
{
    #[Route('/seller/dashbord', name: 'app_seller_dashbord')]
    public function index(): Response
    {
        return $this->render('seller_dashbord/sellerdashbord.html.twig', [
            'controller_name' => 'SellerDashbordController',
        ]);
    }
    #[Route('/seller/dashbord/addproduit', name: 'app_seller_produit')]
    public function produit(): Response
    {
        return $this->render('seller_dashbord/addproduit.html.twig', [
            'controller_name' => 'SellerDashbordController',
        ]);
    }
}
