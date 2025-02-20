<?php
namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;

class PanierControlerController extends AbstractController
{
    // Route to add a product to the cart
    #[Route('/panier/add/{id}', name: 'app_panier_add')]
    public function addToCart($id, ProductRepository $productRepository, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the product from the database
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        // Create new Panier entity
        $panierItem = new Panier();
        $panierItem->setProduct($product);
        $panierItem->setQuantity(1);
        // You might want to calculate subtotal based on product price
        $panierItem->setSubtotal(0); // Set appropriate calculation here

        // Persist to database
        $entityManager->persist($panierItem);
        $entityManager->flush();

        // Add success flash message
        $this->addFlash('success', 'Product added to cart successfully!');

        return $this->redirectToRoute('app_panier_controler');
    }

    // Route to display the cart
    #[Route('/panier', name: 'app_panier_controler')]
    public function viewCart(EntityManagerInterface $entityManager): Response
    {
        // Get all cart items from database
        $panierItems = $entityManager->getRepository(Panier::class)->findAll();

        return $this->render('panier_controler/panier.html.twig', [
            'panierItems' => $panierItems,
        ]);
    }
}




