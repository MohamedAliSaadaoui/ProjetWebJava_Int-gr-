<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;

class PanierControlerController extends AbstractController
{
    // Route to add a product to the cart
    #[Route('/panier/add/{id}', name: 'app_panier_add')]
    public function addToCart($id, ProductRepository $productRepository, SessionInterface $session): Response
    {
        // Retrieve the cart from the session
        $panier = $session->get('panier', []);

        // Retrieve the product from the database
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // Add or update the product in the cart
        if (isset($panier[$id])) {
            // Increment quantity if the product is already in the cart
            $panier[$id]['quantity'] += 1;
        } else {
            // Add new product to the cart with its details (id, name, and initial quantity of 1)
            $panier[$id] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'quantity' => 1, // Set initial quantity to 1
            ];
        }

        // Save the updated cart back into the session
        $session->set('panier', $panier);

        // After adding to cart, render the cart page
        return $this->redirectToRoute('app_panier_controler');
    }

    // Route to display the cart
    #[Route('/panier', name: 'app_panier_controler')]
    public function viewCart(SessionInterface $session): Response
    {
        // Retrieve the cart from the session
        $panier = $session->get('panier', []);

        // Render the panier.html.twig template and pass the cart to it
        return $this->render('panier_controler/panier.html.twig', [
            'panier' => $panier, // Pass the cart to the template
        ]);
    }
}




