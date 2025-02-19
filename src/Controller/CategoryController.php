<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all products from the database
        $products = $entityManager->getRepository(Product::class)->findAll();

        // Render the template and pass the products to it
        return $this->render('category/category.html.twig', [
            'controller_name' => 'CategoryController',
            'products' => $products,  // Passing products to the template
        ]);
    }
}

