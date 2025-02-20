<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;


class PanierControlerController extends AbstractController
{
    #[Route('/panier/add/{id}', name: 'app_panier_add')]
public function addToCart($id, ProductRepository $productRepository, SessionInterface $session): Response
{
    // Retrieve the product from the database
    $product = $productRepository->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Produit non trouvé');
    }

    // Retrieve the cart from the session
    $panier = $session->get('panier', []);

    // Add or update the product in the cart
    if (isset($panier[$id])) {
        $panier[$id]['quantity'] += 1; // Increment quantity if the product is already in the cart
    } else {
        // Add new product to the cart with its details
        $panier[$id] = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'quantity' => 1,
        ];
    }

    // Save the updated cart back into the session
    $session->set('panier', $panier);

    // Redirect back to the cart page
    return $this->redirectToRoute('app_panier_controler');
}
}

