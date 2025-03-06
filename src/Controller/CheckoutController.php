<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\Livraison;
use App\Repository\LivraisonRepository;
use App\Repository\CommandRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Product;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    private $entityManager;
    private $livraisonRepository;
    private $commandRepository;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        LivraisonRepository $livraisonRepository,
        CommandRepository $commandRepository
    ) {
        $this->entityManager = $entityManager;
        $this->livraisonRepository = $livraisonRepository;
        $this->commandRepository = $commandRepository;
    }

    #[Route('/', name: 'checkout')]
    public function checkout(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Get the current command or create a new one
        $command = $this->getCurrentCommand();
        
        // Get all delivery methods
        $livraisonOptions = $this->livraisonRepository->findAll();
        
        // Get cart data from session
        $cart = $session->get('cart', []);
        $cartWithProducts = [];
        $cartTotal = 0;
        
        // Fetch product details for each cart item
        foreach ($cart as $id => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($id);
            if ($product) {
                // Ensure we have numeric values for calculations
                $prix = (float)$product->getPrixDeVente();
                $qty = (int)$quantity;
                
                // Calculate item total and add to cart total
                $itemTotal = $prix * $qty;
                $cartTotal += $itemTotal;
                
                $cartWithProducts[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'total' => $itemTotal
                ];
            }
        }
        
        // Handle form submission
        if ($request->isMethod('POST')) {
            // Get form data
            $livraisonId = $request->request->get('livraison');
            $adresse = $request->request->get('adresse');
            $codePostal = $request->request->get('codePostal');
            $ville = $request->request->get('ville');
            $pays = $request->request->get('pays');
            $paymentMethod = $request->request->get('payment_method');
            
            // Update command with delivery information
            if ($livraisonId) {
                $livraison = $this->livraisonRepository->find($livraisonId);
                
                // Remove existing livraisons if any
                foreach ($command->getLivraisons() as $existingLivraison) {
                    $command->removeLivraison($existingLivraison);
                    $entityManager->remove($existingLivraison);
                }
                
                // Add new livraison to command
                if ($livraison) {
                    $commandLivraison = new Livraison();
                    $commandLivraison->setNom($livraison->getNom());
                    $commandLivraison->setDescription($livraison->getDescription());
                    $commandLivraison->setTarif($livraison->getTarif());
                    $commandLivraison->setDelai($livraison->getDelai());
                    $commandLivraison->setCommand($command);
                    
                    $entityManager->persist($commandLivraison);
                }
            }
            
            // Update address information
            $command->setAdresseLivraison($adresse);
            $command->setCodePostalLivraison($codePostal);
            $command->setVilleLivraison($ville);
            $command->setPaysLivraison($pays);
            
            // Update payment method
            $command->setMethodePaiement($paymentMethod);
            
            // Update order status
            $command->setEtat('confirmed');
            
            // Add products from cart to command
            if (!empty($cartWithProducts)) {
                // First remove any existing products
                foreach ($command->getProducts() as $existingProduct) {
                    $command->getProducts()->removeElement($existingProduct);
                }
                
                // Add products from cart with correct quantities
                foreach ($cartWithProducts as $item) {
                    $product = $item['product'];
                    $quantity = $item['quantity'];
                    
                    // Add product to command the required number of times
                    for ($i = 0; $i < $quantity; $i++) {
                        $command->getProducts()->add($product);
                    }
                }
            }
            
            // Persist changes
            $entityManager->persist($command);
            $entityManager->flush();
            
            // Clear the cart after successful order
            $session->remove('cart');
            
            // Add a flash message
            $this->addFlash('success', 'Votre commande a été passée avec succès!');
            
            // Redirect to appropriate page based on payment method
            if ($paymentMethod === 'stripe') {
                return $this->redirectToRoute('checkout_stripe_payment', ['id' => $command->getId()]);
            } else {
                // For cash on delivery, redirect to confirmation page
                return $this->redirectToRoute('checkout_confirmation', ['id' => $command->getId()]);
            }
        }
        
        // Get selected shipping option if any
        $selectedShipping = null;
        if (!$command->getLivraisons()->isEmpty()) {
            $selectedShipping = $command->getLivraisons()->first();
        }
        
        return $this->render('checkout/checkout.html.twig', [
            'command' => $command,
            'livraisonOptions' => $livraisonOptions,
            'cart' => $cartWithProducts,
            'cartTotal' => $cartTotal,
            'selectedShipping' => $selectedShipping
        ]);
    }
    
    #[Route('/stripe-payment', name: 'checkout_stripe_payment')]
    public function stripePayment(): Response
    {
        $command = $this->getCurrentCommand();
        
        // Set up Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        
        // Debug: Check if products exist
        if ($command->getProducts()->isEmpty()) {
            throw new \Exception('No products found in the command');
        }
        
        $productQuantities = [];
        // Group products by ID and count quantities
        foreach ($command->getProducts() as $product) {
            $productId = $product->getId();
            if (!isset($productQuantities[$productId])) {
                $productQuantities[$productId] = [
                    'product' => $product,
                    'quantity' => 1,
                    'price' => $product->getPrixDeVente()
                ];
            } else {
                $productQuantities[$productId]['quantity']++;
            }
        }

        // Debug: Print product quantities
        dump($productQuantities);
        
        $lineItems = [];
        foreach ($productQuantities as $item) {
            // Ensure price is converted to cents and is an integer
            $unitAmount = (int)round($item['price'] * 100);
            
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['product']->getObjetAVendre(),
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $item['quantity'],
            ];
        }
        
        // Add delivery cost if present
        if (!$command->getLivraisons()->isEmpty()) {
            $livraison = $command->getLivraisons()->first();
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Livraison - ' . $livraison->getNom(),
                    ],
                    'unit_amount' => (int)round($livraison->getTarif() * 100),
                ],
                'quantity' => 1,
            ];
        }
        
        // Debug: Check final line items
        if (empty($lineItems)) {
            throw new \Exception('No line items generated for Stripe');
        }
        dump($lineItems);
        
        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('checkout_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('checkout_stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
            
            // Save Stripe session ID to command
            $command->setStripeSessionId($session->id);
            $this->entityManager->flush();
            
            return $this->redirect($session->url);
        } catch (\Exception $e) {
            // Log the error and show a user-friendly message
            $this->addFlash('error', 'Une erreur est survenue lors de la création de la session de paiement.');
            return $this->redirectToRoute('checkout');
        }
    }
    
    #[Route('/stripe-success', name: 'checkout_stripe_success')]
    public function stripeSuccess(): Response
    {
        $command = $this->getCurrentCommand();
        
        // Mark as paid
        $command->setPaye(true);
        $command->setEtat('paid');
        $this->entityManager->flush();
        
        return $this->redirectToRoute('checkout_confirmation');
    }
    
    #[Route('/stripe-cancel', name: 'checkout_stripe_cancel')]
    public function stripeCancel(): Response
    {
        return $this->redirectToRoute('checkout');
    }
    
    #[Route('/confirmation', name: 'checkout_confirmation')]
    public function confirmation(): Response
    {
        $command = $this->getCurrentCommand();
        
        return $this->render('checkout/confirmation.html.twig', [
            'command' => $command
        ]);
    }
    
    #[Route('/invoice/{id}', name: 'checkout_invoice')]
    public function generateInvoice(Command $command, PdfService $pdfService): Response
    {
        // Check if user is authorized to view this invoice
        if ($command->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You are not authorized to view this invoice');
        }
        
        $html = $this->renderView('checkout/invoice.html.twig', [
            'command' => $command
        ]);
        
        $filename = 'facture-' . $command->getId() . '.pdf';
        
        return $pdfService->generatePdfResponse($html, $filename);
    }
    
    private function getCurrentCommand(): Command
    {
        $user = $this->getUser();
        
        // Find an existing command in 'cart' state
        $command = $this->commandRepository->findOneBy([
            'user' => $user,
            'etat' => 'cart'
        ]);
        
        if (!$command) {
            // Create a new command
            $command = new Command();
            $command->setUser($user);
            $command->setEtat('cart');
            $this->entityManager->persist($command);
            $this->entityManager->flush();
        }
        
        return $command;
    }
} 