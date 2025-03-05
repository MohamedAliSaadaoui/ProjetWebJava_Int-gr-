<?php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

final class AllproductSellerController extends AbstractController
{
    #[Route('/allproduct/seller', name: 'app_allproduct_seller')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('allproduct_seller/allproductseller.html.twig', [
            'products' => $products,
        ]);
    }
}


 


