<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;

class PanierControlerController extends AbstractController
{
    // Route to add a product to the cart
    #[Route('/panier/add/{id}', name: 'app_panier_add')]
    public function addToCart($id, ProductRepository $productRepository, SessionInterface $session): Response
    {
        // Retrieve the product from the database
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        // Get the cart from the session (if it doesn't exist, create an empty one)
        $cart = $session->get('cart', []);

        // Check if the product is already in the cart
        if (isset($cart[$id])) {
            // If it is, increase the quantity
            $cart[$id]['quantity']++;
        } else {
            // If it's not, add it to the cart with the product name and quantity
            $cart[$id] = [
                'name' => $product->getObjetAVendre(),
                'quantity' => 1,
                'price' => $product->getPrixDeVente(),
            ];
        }

        // Save the cart back to the session
        $session->set('cart', $cart);

        // Add success flash message
        $this->addFlash('success', 'Product added to cart successfully!');

        return $this->redirectToRoute('app_panier_controler');
    }

    // Route to display the cart
    #[Route('/panier', name: 'app_panier_controler')]
    public function viewCart(SessionInterface $session, ProductRepository $productRepository): Response
    {
        // Get all cart items from the session
        $cart = $session->get('cart', []);

        // Fetch the full product details based on the cart items' product IDs
        $cartItems = [];

        foreach ($cart as $id => $item) {
            // Fetch the product details for each item in the cart using its ID
            $product = $productRepository->find($id);

            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                ];
            }
        }

        // Render the cart page with the fetched product details
        return $this->render('panier_controler/panier.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    // Route to remove a product from the cart
    #[Route('/panier/remove/{id}', name: 'app_panier_remove')]
    public function removeFromCart($id, SessionInterface $session): Response
    {
        // Get the cart from the session
        $cart = $session->get('cart', []);

        // Remove the product from the cart
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        // Save the updated cart to the session
        $session->set('cart', $cart);

        // Redirect to the cart page
        return $this->redirectToRoute('app_panier_controler');
    }
}
