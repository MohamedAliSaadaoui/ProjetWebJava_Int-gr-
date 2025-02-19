<?php
namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductTypeControler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ProductManagementController extends AbstractController
{
    #[Route('/seller/add-product', name: 'app_add_product')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        
        // Create a new Product entity
    $product = new Product();

    // Create the form
    $form = $this->createForm(ProductTypeControler::class, $product);

    // Handle the form submission
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {   
        // Persist the product to the database
        $entityManager->persist($product);
        $entityManager->flush();
    
        // Optionally, add a success message
        $this->addFlash('success', 'Product added successfully!');
        
        // Pass product data to the view
        return $this->render('seller_dashbord/addproduit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    // Render the form template if not submitted or not valid
    return $this->render('seller_dashbord/addproduit.html.twig', [
        'form' => $form->createView(),
    ]);
    }
    
}
