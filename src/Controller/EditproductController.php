<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductTypeControler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;  // Add this
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;  // Add this for createNotFoundException


final class EditproductController extends AbstractController
{
    #[Route('/editproduct/{id}', name: 'app_editproduct')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the product by its ID
        $product = $entityManager->getRepository(Product::class)->find($id);

        // Check if the product exists
        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        // Create the form for editing the product
        $form = $this->createForm(ProductTypeControler::class, $product);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the updated product to the database
            $entityManager->flush();

            // No redirect, just return the updated product details in the same page
            return $this->render('editproduct/index.html.twig', [
                'form' => $form->createView(),
                'product' => $product
            ]);
        }

        // Render the form if it was not submitted or is invalid
        return $this->render('editproduct/index.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }
}
