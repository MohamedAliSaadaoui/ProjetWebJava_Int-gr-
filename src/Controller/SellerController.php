<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SellerController extends AbstractController
{
    #[Route('/sellerlogin', name: 'app_seller')]
    public function index(): Response
    {
        return $this->render('seller/sellerlogin.html.twig', [
            'controller_name' => 'SellerController',
        ]);
    }
}
