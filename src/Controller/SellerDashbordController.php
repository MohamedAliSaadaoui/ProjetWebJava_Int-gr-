<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class SellerDashbordController extends AbstractController
{
    #[Route('/seller/dashbord', name: 'app_seller_dashbord')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get current logged in user
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Fetch products belonging to this user
        $products = $entityManager->getRepository(Product::class)
            ->findBy(['user' => $user], ['id' => 'DESC']);
        
        return $this->render('seller_dashbord/sellerdashbord.html.twig', [
            'controller_name' => 'SellerDashbordController',
            'products' => $products,
        ]);
    }
    
    // ... other methods
}