<?php

namespace App\Controller;

use App\Entity\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/user/orders', name: 'app_user_orders')]
    public function userOrders(EntityManagerInterface $entityManager): Response
    {
        // Make sure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Get current user's orders
        $orders = $entityManager
            ->getRepository(Command::class)
            ->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']);
        
        return $this->render('panier/orders.html.twig', [
            'orders' => $orders
        ]);
    }
    
    #[Route('/order/details/{id}', name: 'app_order_details')]
    public function orderDetails(Command $order): Response
    {
        // Ensure user can only see their own orders
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot view this order');
        }
        
        return $this->render('panier/order_details.html.twig', [
            'order' => $order
        ]);
    }
} 