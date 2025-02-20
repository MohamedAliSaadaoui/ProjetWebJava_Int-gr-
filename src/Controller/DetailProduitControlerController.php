<?php
namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DetailProduitControlerController extends AbstractController
{
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
}