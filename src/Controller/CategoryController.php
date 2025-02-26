<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all products from the database
        $products = $entityManager->getRepository(Product::class)->findAll();
        // Get the total number of products
        $totalProducts = count($products);

        // Render the template and pass the products to it
        return $this->render('category/category.html.twig', [
            'controller_name' => 'CategoryController',
            'products' => $products,  // Passing products to the template
            'totalProducts' => $totalProducts,  // Pass the total count
        ]);
    }

    #[Route('/panier/add/{id}/{name}', name: 'app_panier_add')]
    public function addToCart(int $id, string $name, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Fetch the product from the database by its ID
        $product = $entityManager->getRepository(Product::class)->find($id);

        // If the product doesn't exist, redirect to the category page
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_category');
        }

        // Get the cart from the session, or initialize an empty array if it doesn't exist
        $cart = $session->get('cart', []);

        // Check if the product is already in the cart
        if (isset($cart[$id])) {
            $cart[$id]['quantity']++; // Increment the quantity if the product is already in the cart
        } else {
            // Otherwise, add the product to the cart with quantity 1
            $cart[$id] = [
                'name' => $name,
                'quantity' => 1,
                'price' => $product->getPrixDeVente(), // Using getPrixDeVente instead of getPrice
            ];
        }

        // Save the updated cart back into the session
        $session->set('cart', $cart);

        // Redirect to the category page with a success message
        $this->addFlash('success', 'Product added to cart!');
        return $this->redirectToRoute('app_category');
    }
}
