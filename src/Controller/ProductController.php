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
use App\Entity\Favorite; // Import the Favorite entity
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


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
    
    

    #[Route('/product/{id}/favorite', name: 'app_add_to_favorite')]
    public function addToFavorite(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);
        $user = $this->getUser();
    
        if (!$user instanceof User) {
            return new JsonResponse(['success' => false, 'message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
    
        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
    
        // Check if favorite already exists
        $existingFavorite = $entityManager->getRepository(Favorite::class)->findOneBy([
            'user' => $user,
            'product' => $product,
        ]);
    
        if ($existingFavorite) {
            return new JsonResponse(['success' => false, 'message' => 'Product already in favorites'], Response::HTTP_CONFLICT);
        }
    
        $favorite = new Favorite();
        $favorite->setProduct($product);
        $favorite->setUser($user);
    
        $entityManager->persist($favorite);
        $entityManager->flush();
    
        return new JsonResponse(['success' => true, 'message' => 'Product added to favorites!']);
    }

    #[Route('/Favorite', name: 'app_favorites')]
public function showFavorites(EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser(); // Get the logged-in user

    if (!$user instanceof User) {
        $this->addFlash('error', 'User not logged in!');
        return $this->redirectToRoute('app_category');
    }

    // Fetch favorite products for the user
    $favorites = $entityManager->getRepository(Favorite::class)->findBy(['user' => $user]);

    if (empty($favorites)) {
        $this->addFlash('error', 'No favorite products found!');
    }

    return $this->render('favoratepage/Favorite.html.twig', [
        'favorites' => $favorites,
    ]);
}


    // add product 
    #[Route('/seller/add-product', name: 'app_add_product')]
    public function addProduct(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user for the product
            $user = $security->getUser();
            
            // If no user is logged in, use a default user
            if (!$user instanceof User) {
                $user = $this->getOrCreateDefaultUser($entityManager);
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
            return $this->redirectToRoute('app_allproduct_seller');
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
    // sorted by time and price 
    

    
    // show all product 
    #[Route('/category', name: 'app_category')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get filter parameters
        $category = $request->query->get('category');
        $minPrice = $request->query->get('min');
        $maxPrice = $request->query->get('max');
        $sort = $request->query->get('sort', 'date_desc');
        
        // Add pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12; // Items per page
        
        // Create query builder
        $queryBuilder = $entityManager->getRepository(Product::class)->createQueryBuilder('p');
        
        // Apply any existing filters
        if ($category) {
            $queryBuilder->andWhere('p.genre = :category')
                         ->setParameter('category', $category);
        }
        
        if ($minPrice !== null) {
            $queryBuilder->andWhere('p.prixDeVente >= :minPrice')
                         ->setParameter('minPrice', $minPrice);
        }
        
        if ($maxPrice !== null) {
            $queryBuilder->andWhere('p.prixDeVente <= :maxPrice')
                         ->setParameter('maxPrice', $maxPrice);
        }
        
        // Apply sorting
        switch ($sort) {
            case 'genre_asc':
                $queryBuilder->orderBy('p.genre', 'ASC');
                break;
            case 'genre_desc':
                $queryBuilder->orderBy('p.genre', 'DESC');
                break;
            case 'price_asc':
                $queryBuilder->orderBy('p.prixDeVente', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('p.prixDeVente', 'DESC');
                break;
            default:
                $queryBuilder->orderBy('p.id', 'DESC');
                break;
        }
        
        // Get total items for pagination
        $countQuery = clone $queryBuilder;
        $totalItems = count($countQuery->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);
        
        // Add pagination limit
        $queryBuilder->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit);
        
        // Execute query
        $products = $queryBuilder->getQuery()->getResult();
        
        // Return the response with pagination data added
        return $this->render('category/category.html.twig', [
            'controller_name' => 'CategoryController',
            'products' => $products,
            'categories' => $entityManager->getRepository(Product::class)->findAll(),
            'totalProducts' => $totalItems,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit
        ]);
    }
        //show product's in the landing page 
    
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

    /**
     * @Route("/product/favorite/{id}", name="app_product_favorite_toggle", methods={"POST"})
     */
    #[Route('/product/favorite/{id}', name: 'app_product_favorite_toggle', methods: ['POST'])]
    public function toggleFavorite(Product $product, Request $request): Response
    {
        // Get the session
        $session = $request->getSession();
        
        // Get current favorites from session
        $favorites = $session->get('favorites', []);
        
        $productId = $product->getId();
        $isInFavorites = false;
        
        // Check if product is already in favorites
        if (in_array($productId, $favorites)) {
            // Remove from favorites
            $favorites = array_filter($favorites, function($id) use ($productId) {
                return $id != $productId;
            });
            $message = 'Product removed from favorites';
        } else {
            // Add to favorites
            $favorites[] = $productId;
            $message = 'Product added to favorites';
            $isInFavorites = true;
        }
        
        // Save updated favorites to session
        $session->set('favorites', $favorites);
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true, 
                'message' => $message,
                'isInFavorites' => $isInFavorites
            ]);
        }
        
        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_detail_produit_controler', ['id' => $product->getId()]);
    }

    /**
     * @Route("/product/favorite/check/{id}", name="app_product_favorite_check", methods={"GET"})
     */
    #[Route('/product/favorite/check/{id}', name: 'app_product_favorite_check', methods: ['GET'])]
    public function checkFavorite(Product $product, Request $request): JsonResponse
    {
        // Get the session
        $session = $request->getSession();
        
        // Get current favorites from session
        $favorites = $session->get('favorites', []);
        
        // Check if product is in favorites
        $isInFavorites = in_array($product->getId(), $favorites);
        
        return new JsonResponse(['isInFavorites' => $isInFavorites]);
    }

    /**
     * @Route("/favorites", name="app_product_favorites")
     */
    #[Route('/favorites', name: 'app_product_favorites')]
    public function favorites(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the session
        $session = $request->getSession();
        
        // Get current favorites from session
        $favoriteIds = $session->get('favorites', []);
        
        $favoriteProducts = [];
        
        if (!empty($favoriteIds)) {
            // Get products by IDs
            $favoriteProducts = $entityManager->getRepository(Product::class)
                ->findBy(['id' => $favoriteIds]);
        }
        
        return $this->render('favoratepage/Favorite.html.twig', [
            'favoriteProducts' => $favoriteProducts,
        ]);
    }

    /**
     * @Route("/product/favorite/count", name="app_product_favorite_count", methods={"GET"})
     */
    #[Route('/product/favorite/count', name: 'app_product_favorite_count', methods: ['GET'])]
    public function getFavoriteCount(Request $request): JsonResponse
    {
        // Get the session
        $session = $request->getSession();
        
        // Get current favorites from session
        $favorites = $session->get('favorites', []);
        
        // Return the count
        return new JsonResponse(['count' => count($favorites)]);
    }

    /**
     * @Route("/product/favorite/check-multiple", name="app_product_favorite_check_multiple", methods={"POST"})
     */
    #[Route('/product/favorite/check-multiple', name: 'app_product_favorite_check_multiple', methods: ['POST'])]
    public function checkMultipleFavorites(Request $request): JsonResponse
    {
        // Get the session
        $session = $request->getSession();
        
        // Get current favorites from session
        $favorites = $session->get('favorites', []);
        
        // Get product IDs from request
        $data = json_decode($request->getContent(), true);
        $productIds = $data['productIds'] ?? [];
        
        // Filter product IDs to only those in favorites
        $favoriteIds = array_filter($productIds, function($id) use ($favorites) {
            return in_array($id, $favorites);
        });
        
        return new JsonResponse(['favoriteIds' => array_values($favoriteIds)]);
    }

    /**
     * Search products by name
     * 
     * @Route("/search", name="app_product_search")
     */
    #[Route('/search', name: 'app_product_search')]
    public function search(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the search query from the request
        $query = $request->query->get('search', '');
        
        // Add pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12; // Items per page
        
        // Create query builder
        $queryBuilder = $entityManager->getRepository(Product::class)->createQueryBuilder('p');
        
        // Apply search filter if query is not empty
        if (!empty($query)) {
            $queryBuilder->andWhere('p.objetAVendre LIKE :query OR p.description LIKE :query')
                         ->setParameter('query', '%' . $query . '%');
        }
        
        // Order by newest products
        $queryBuilder->orderBy('p.id', 'DESC');
        
        // Get total items for pagination
        $countQuery = clone $queryBuilder;
        $totalItems = count($countQuery->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);
        
        // Add pagination limit
        $queryBuilder->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit);
        
        // Execute query
        $products = $queryBuilder->getQuery()->getResult();
        
        // Return the response with pagination data added
        return $this->render('category/category.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
            'totalProducts' => $totalItems,
            'searchQuery' => $query,
            'isSearchResult' => true,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit
        ]);
    }
}