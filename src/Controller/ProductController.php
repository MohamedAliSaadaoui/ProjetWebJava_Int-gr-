<?php
namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

class ProductController extends AbstractController
{ 
    private $entityManager;
    private $requestStack;
    private $validator;
    
    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->validator = $validator;
    }
    
    // add product 
    #[Route('/seller/add-product', name: 'app_add_product')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get or create a default user
            $user = $this->getOrCreateDefaultUser($entityManager);
            
            $product->setUser($user); // Set the user
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Product added successfully!');

            return $this->redirectToRoute('app_allproduct_seller'); // Redirect to product list page
        }

        return $this->render('seller_dashbord/addproduit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
     
    
    
    /**
     * Validate product data with custom constraints
     * 
     * @param Product $product
     * @return array Array of error messages
     */
    private function validateProductData(Product $product): array
    {
        $errors = [];
        
        // Product Name validation
        if (empty($product->getObjetAVendre())) {
            $errors[] = 'Product name is required.';
        } elseif (strlen($product->getObjetAVendre()) < 3) {
            $errors[] = 'Product name must be at least 3 characters long.';
        } elseif (strlen($product->getObjetAVendre()) > 255) {
            $errors[] = 'Product name cannot exceed 255 characters.';
        }
        
        // Description validation
        if (empty($product->getDescription())) {
            $errors[] = 'Description is required.';
        } elseif (strlen($product->getDescription()) < 10) {
            $errors[] = 'Description must be at least 10 characters long.';
        }
        
        // Genre validation
        if (empty($product->getGenre())) {
            $errors[] = 'Category/Genre is required.';
        } elseif (!in_array($product->getGenre(), ['Homme', 'Femme', 'Enfant', 'Unisexe'])) {
            $errors[] = 'Please select a valid category.';
        }
        
        // Condition validation
        if (empty($product->getEtat())) {
            $errors[] = 'Condition is required.';
        } elseif (!in_array($product->getEtat(), ['Neuf', 'Utilisé', 'Reconditionné'])) {
            $errors[] = 'Please select a valid condition.';
        }
        
        // Size validation
        if (empty($product->getTaille())) {
            $errors[] = 'Size is required.';
        } elseif (!in_array($product->getTaille(), ['XS', 'S', 'M', 'L', 'XL', 'XXL'])) {
            $errors[] = 'Please select a valid size.';
        }
        
        // Color validation
        if (empty($product->getCouleur())) {
            $errors[] = 'Color is required.';
        }
        
        // Price validation
        if (empty($product->getPrixDeVente()) || $product->getPrixDeVente() <= 0) {
            $errors[] = 'Selling price must be greater than zero.';
        }
        
        if (empty($product->getPrixOriginal()) || $product->getPrixOriginal() <= 0) {
            $errors[] = 'Original price must be greater than zero.';
        }
        
        // Phone validation
        if (empty($product->getTelephone())) {
            $errors[] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9]{10}$/', $product->getTelephone())) {
            $errors[] = 'Please enter a valid 10-digit phone number.';
        }
        
        // Email validation
        if (empty($product->getEmail())) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($product->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Address validation
        if (empty($product->getAdresse())) {
            $errors[] = 'Address is required.';
        }
        
        // Postal code validation
        if (empty($product->getCodePostal())) {
            $errors[] = 'Postal code is required.';
        } elseif (!preg_match('/^[0-9]{5}$/', $product->getCodePostal())) {
            $errors[] = 'Please enter a valid 5-digit postal code.';
        }
        
        // City validation
        if (empty($product->getVille())) {
            $errors[] = 'City is required.';
        }
        
        return $errors;
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

        if ($form->isSubmitted()) {
            // Custom validation
            $errors = $this->validateProductData($product);
            
            if (count($errors) > 0) {
                // Add validation errors as flash messages
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            } else {
                // Make sure the product has a user assigned
                if (!$product->getUser()) {
                    $user = $this->getOrCreateDefaultUser($entityManager);
                    $product->setUser($user);
                }
                
                // Persist the updated product to the database
                $entityManager->flush();
                
                $this->addFlash('success', 'Product updated successfully!');
                return $this->redirectToRoute('app_allproduct_seller');
            }
        }

        // Render the form if it was not submitted or is invalid
        return $this->render('editproduct/index.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }
    
    /**
     * Get an existing default user or create one if it doesn't exist
     * 
     * @param EntityManagerInterface $entityManager
     * @return User
     */
    private function getOrCreateDefaultUser(EntityManagerInterface $entityManager): User
    {
        // Try to fetch the user with ID 1
        $user = $entityManager->getRepository(User::class)->find(1);
        
        // If no user exists, create a default one
        if (!$user) {
            $user = new User();
            $user->setUsername('defaultuser');
            $user->setEmail('default@example.com');
            $user->setPassword(password_hash('defaultpassword', PASSWORD_BCRYPT));
            $user->setRoles('ROLE_USER');
            $user->setCreatedAt(new \DateTime());
            $entityManager->persist($user);
            $entityManager->flush(); // Save the user first
        }
        
        return $user;
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
    //product detail 
    #[Route('/produit/{id}', name: 'app_detail_produit_controler')]
    public function detailProduit($id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
        
        return $this->render('detail_produit_controler/detailproduit.html.twig', [
            'product' => $product,
            
        ]);
    }
    
    // add to cart 
    #[Route('/panier/add/{id}', name: 'app_panier_add')]
    public function addToCart(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the session from the RequestStack
        $session = $this->requestStack->getSession();
        
        // Fetch the product from the database by its ID
        $product = $entityManager->getRepository(Product::class)->find($id);

        // If the product doesn't exist, return error response
        if (!$product) {
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }
            $this->addFlash('error', 'Produit non trouvé.');
            return $this->redirectToRoute('app_category');
        }

        // Get the cart from the session, or initialize an empty array if it doesn't exist
        $cart = $session->get('cart', []);

        // Get quantity from request, default to 1 if not specified
        $quantity = $request->query->getInt('quantity', 1);

        // Check if the product is already in the cart
        if (isset($cart[$id])) {
            // Increment the quantity if the product is already in the cart
            $cart[$id]['quantity'] += $quantity;
        } else {
            // Add the product to the cart with all necessary details
            $cart[$id] = [
                'id' => $product->getId(),
                'objetAVendre' => $product->getObjetAVendre(),
                'quantity' => $quantity,
                'prixDeVente' => $product->getPrixDeVente(),
                'genre' => $product->getGenre(),
                'taille' => $product->getTaille(),
                'couleur' => $product->getCouleur(),
                'etat' => $product->getEtat()
            ];
        }

        // Save the updated cart back into the session
        $session->set('cart', $cart);

        // Calculate cart totals
        $totalQuantity = 0;
        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalQuantity += $item['quantity'];
            $totalPrice += $item['quantity'] * $item['prixDeVente'];
        }
        
        // Store cart totals in session
        $session->set('cart_total_quantity', $totalQuantity);
        $session->set('cart_total_price', $totalPrice);

        // If this is an AJAX request, return JSON response
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cartTotalQuantity' => $totalQuantity,
                'cartTotalPrice' => $totalPrice
            ]);
        }

        // Redirect to the cart page with a success message
        $this->addFlash('success', 'Produit ajouté au panier !');
        return $this->redirectToRoute('app_panier');
    }

    // Display cart contents
    #[Route('/panier', name: 'app_panier')]
    public function showCart(): Response
    {
        // Get the cart from session
        $cart = $this->requestStack->getSession()->get('cart', []);
        $totalQuantity = $this->requestStack->getSession()->get('cart_total_quantity', 0);
        $totalPrice = $this->requestStack->getSession()->get('cart_total_price', 0);

        return $this->render('panier/panier.html.twig', [
            'cart' => $cart,
            'totalQuantity' => $totalQuantity,
            'totalPrice' => $totalPrice
        ]);
    }

    // Remove item from cart
    #[Route('/panier/remove/{id}', name: 'app_panier_remove')]
    public function removeFromCart(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            // Recalculate totals
            $totalQuantity = $session->get('cart_total_quantity', 0) - $cart[$id]['quantity'];
            $totalPrice = $session->get('cart_total_price', 0) - ($cart[$id]['quantity'] * $cart[$id]['prixDeVente']);
            
            // Remove the item
            unset($cart[$id]);
            
            // Update session
            $session->set('cart', $cart);
            $session->set('cart_total_quantity', $totalQuantity);
            $session->set('cart_total_price', $totalPrice);
            
            $this->addFlash('success', 'Produit retiré du panier');
        }

        return $this->redirectToRoute('app_panier');
    }

    // Update cart quantity
    #[Route('/panier/update/{id}', name: 'app_panier_update', methods: ['POST'])]
    public function updateCartQuantity(int $id, Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', []);
        $quantity = $request->request->getInt('quantity', 1);

        if (isset($cart[$id])) {
            // Calculate the difference for totals
            $quantityDiff = $quantity - $cart[$id]['quantity'];
            $priceDiff = $quantityDiff * $cart[$id]['prixDeVente'];
            
            // Update item quantity
            $cart[$id]['quantity'] = $quantity;
            
            // Update totals
            $totalQuantity = $session->get('cart_total_quantity', 0) + $quantityDiff;
            $totalPrice = $session->get('cart_total_price', 0) + $priceDiff;
            
            // Update session
            $session->set('cart', $cart);
            $session->set('cart_total_quantity', $totalQuantity);
            $session->set('cart_total_price', $totalPrice);
            
            $this->addFlash('success', 'Quantité mise à jour');
        }

        return $this->redirectToRoute('app_panier');
    }
}