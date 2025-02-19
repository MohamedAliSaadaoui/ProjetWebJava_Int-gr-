<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteproductController extends AbstractController
{
    #[Route('/deleteproduct/{id}', name: 'app_deleteproduct')]
    public function delete(int $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Find the product by its ID
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            // If the product is not found, throw an exception
            $this->addFlash('error', 'Product not found!');
            return $this->redirectToRoute('app_allproduct_seller'); // Redirect back to product list
        }

        // Remove the product from the database
        $entityManager->remove($product);
        $entityManager->flush();

        // Add a success message
        $this->addFlash('success', 'Product deleted successfully.');

        // Redirect back to the product list page
        return $this->redirectToRoute('app_allproduct_seller');
    }
}

