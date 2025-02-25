<?php
namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



class ProductController extends AbstractController
{ 
    // crud product 
    // add product 
    #[Route('/seller/add-product', name: 'app_add_product')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        
        $product = new Product();
    
        // Create the form
        $form = $this->createForm(ProductType::class, $product);
    
        // Handle the form submission
        $form->handleRequest($request);
      
        


        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the product to the database
            $entityManager->persist($product);
            $entityManager->flush();
    
            // Add a success message
            $this->addFlash('success', 'Product added successfully!');
    
            return $this->redirectToRoute('app_allproduct_seller');
        }
    
        // Render the form template if not submitted or not valid
        return $this->render('seller_dashbord/addproduit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    //delete product
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
    // edit product 
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
        $form = $this->createForm(ProductType::class, $product);

        // Handle the form submission
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the updated product to the database
            $entityManager->flush();

            return $this->redirectToRoute('app_allproduct_seller');
        }

        // Render the form if it was not submitted or is invalid
        return $this->render('editproduct/index.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    } 
    // show all product 
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
    // crud product 
    // add to cart 
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
                'price' => $product->getPrice(), // Assuming you have a price field in your Product entity
            ];
        }

        // Save the updated cart back into the session
        $session->set('cart', $cart);

        // Redirect to the category page with a success message
        $this->addFlash('success', 'Product added to cart!');
        return $this->redirectToRoute('app_category');
    }
    // remove product 

    // show cart product
    
}