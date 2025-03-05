<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AdminDashBoardController extends AbstractController
{
    #[Route('/admin/dash/board', name: 'app_admin_dash_board')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Get latest products for dashboard display
        $products = $entityManager->getRepository(Product::class)->findBy([], ['id' => 'DESC'], 10);
        
        // Get total count of products
        $productCount = $entityManager->getRepository(Product::class)->count([]);
        
        // Get count of users
        $userCount = $entityManager->getRepository(User::class)->count([]);
        
        return $this->render('admin_dash_board/admindashbord.html.twig', [
            'controller_name' => 'AdminDashBoardController',
            'products' => $products,
            'productCount' => $productCount,
            'userCount' => $userCount,
        ]);
    }
    
    #[Route('/admin/add-product', name: 'app_admin_add_product')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user for the product (admin or specified user)
            $user = $security->getUser();
            
            // If no user is logged in, redirect to login
            if (!$user instanceof User) {
                return $this->redirectToRoute('app_login');
            }
            
            $product->setUser($user);
            
            // Handle file upload
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Generate a safe filename
                $safeFilename = preg_replace('/[^A-Za-z0-9]/', '_', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                // Move the file to the directory where product photos are stored
                try {
                    $photoFile->move(
                        $this->getParameter('product_photos_directory'),
                        $newFilename
                    );
                    
                    // Update the 'photo' property to store the file name
                    $product->setPhoto($newFilename);
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                    $this->addFlash('error', 'Failed to upload product photo: ' . $e->getMessage());
                }
            }
            
            // Save the product
            $entityManager->persist($product);
            $entityManager->flush();
            
            $this->addFlash('success', 'Product added successfully!');
            return $this->redirectToRoute('app_admin_dash_board');
        }
        
        return $this->render('admin_dash_board/adminaddproduct.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/admin/products', name: 'app_admin_products')]
    public function listProducts(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();
        
        return $this->render('admin_dash_board/products.html.twig', [
            'products' => $products,
        ]);
    }
    
    #[Route('/admin/product/{id}/edit', name: 'app_admin_edit_product')]
    public function editProduct(Request $request, EntityManagerInterface $entityManager, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Generate a safe filename
                $safeFilename = preg_replace('/[^A-Za-z0-9]/', '_', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                // Move the file to the directory where product photos are stored
                try {
                    $photoFile->move(
                        $this->getParameter('product_photos_directory'),
                        $newFilename
                    );
                    
                    // Update the 'photo' property to store the file name
                    $product->setPhoto($newFilename);
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                    $this->addFlash('error', 'Failed to upload product photo: ' . $e->getMessage());
                }
            }
            
            // Save the changes
            $entityManager->flush();
            
            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('app_admin_products');
        }
        
        return $this->render('admin_dash_board/editproduct.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }
    
    #[Route('/admin/product/{id}/delete', name: 'app_admin_delete_product', methods: ['POST'])]
    public function deleteProduct(Request $request, EntityManagerInterface $entityManager, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            
            $this->addFlash('success', 'Product deleted successfully!');
        }
        
        return $this->redirectToRoute('app_admin_products');
    }
}
