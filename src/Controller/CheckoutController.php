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
    public function checkout(Request $request): Response
    {
        // Get the current command or create a new one
        $command = $this->getCurrentCommand();
        
        // Get all delivery methods
        $livraisonOptions = $this->livraisonRepository->findAll();
        
        if ($request->isMethod('POST')) {
            // Handle delivery method selection
            if ($request->request->has('livraison')) {
                $livraisonId = $request->request->get('livraison');
                $livraison = $this->livraisonRepository->find($livraisonId);
                
                if ($livraison) {
                    // Clear existing livraisons
                    foreach ($command->getLivraisons() as $existingLivraison) {
                        $command->removeLivraison($existingLivraison);
                        $this->entityManager->remove($existingLivraison);
                    }
                    
                    // Add the selected livraison
                    $command->addLivraison($livraison);
                    $this->entityManager->persist($livraison);
                    $this->entityManager->flush();
                }
            }
            
            // Handle address information
            if ($request->request->has('adresse')) {
                $command->setAdresseLivraison($request->request->get('adresse'));
                $command->setCodePostalLivraison($request->request->get('codePostal'));
                $command->setVilleLivraison($request->request->get('ville'));
                $command->setPaysLivraison($request->request->get('pays'));
                $this->entityManager->flush();
            }
            
            // Handle payment method
            if ($request->request->has('payment_method')) {
                $paymentMethod = $request->request->get('payment_method');
                $command->setMethodePaiement($paymentMethod);
                
                // Calculate and set the total
                $command->setTotalCommande($command->calculateTotal());
                $this->entityManager->flush();
                
                if ($paymentMethod === 'cash_on_delivery') {
                    // For cash on delivery, mark as pending and go to confirmation
                    $command->setEtat('pending');
                    $this->entityManager->flush();
                    
                    return $this->redirectToRoute('checkout_confirmation');
                } else if ($paymentMethod === 'stripe') {
                    // For Stripe, redirect to Stripe payment
                    return $this->redirectToRoute('checkout_stripe_payment');
                }
            }
        }
        
        return $this->render('checkout/checkout.html.twig', [
            'command' => $command,
            'livraisonOptions' => $livraisonOptions
        ]);
    }
    
    #[Route('/stripe-payment', name: 'checkout_stripe_payment')]
    public function stripePayment(): Response
    {
        $command = $this->getCurrentCommand();
        
        // Set up Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        
        $lineItems = [];
        foreach ($command->getProducts() as $product) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getObjetAVendre(),
                    ],
                    'unit_amount' => $product->getPrixDeVente() * 100, // in cents
                ],
                'quantity' => 1,
            ];
        }
        
        // Add delivery cost
        if (!$command->getLivraisons()->isEmpty()) {
            $livraison = $command->getLivraisons()->first();
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Livraison - ' . $livraison->getNom(),
                    ],
                    'unit_amount' => $livraison->getTarif() * 100, // in cents
                ],
                'quantity' => 1,
            ];
        }
        
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('checkout_stripe_success', [], 0),
            'cancel_url' => $this->generateUrl('checkout_stripe_cancel', [], 0),
        ]);
        
        // Save Stripe session ID to command
        $command->setStripeSessionId($session->id);
        $this->entityManager->flush();
        
        return $this->redirect($session->url);
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