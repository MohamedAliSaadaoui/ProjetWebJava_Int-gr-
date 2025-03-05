<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Form\DemandeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DemandeRepository; 

class PublicDemandeController extends AbstractController
{
    #[Route('/demande/nouvelle', name: 'app_demande_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $demande = new Demande();
        $demande->setStatut('En cours');
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($demande);
            $entityManager->flush();

            return $this->redirectToRoute('app_public_demande_confirmation');
        }

        return $this->render('public_demande/index.html.twig', [
            'form' => $form->createView(),
        ]);  
    }

    #[Route('/demande/confirmation', name: 'app_public_demande_confirmation')]
    public function confirmation(): Response
    {
        return $this->render('public_demande/confirmation.html.twig');

    } 

    #[Route('/demande/liste', name: 'app_demande_list')]
    public function list(DemandeRepository $demandeRepository): Response
    {
        $demandes = $demandeRepository->findAll();

        return $this->render('public_demande/list.html.twig', [
            'demandes' => $demandes, 
        ]);
    }

    #[Route('/demande/modifier/{id}', name: 'app_demande_edit')]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager, DemandeRepository $demandeRepository  ): Response
{

    $demande = $demandeRepository->find($id);

    if (!$demande) {
        throw $this->createNotFoundException('Demande non trouvée');
    }
    $form = $this->createForm(DemandeType::class, $demande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        dump($demande);
        $entityManager->flush();
        return $this->redirectToRoute('app_demande_list');
    }

    return $this->render('public_demande/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/demande/supprimer/{id}', name: 'app_demande_delete')]
public function delete(int $id, EntityManagerInterface $entityManager, DemandeRepository $demandeRepository): Response
{
    
    $demande = $demandeRepository->find($id);

    
    if (!$demande) {
        throw $this->createNotFoundException('La demande n\'existe pas.');
    }

    
    $entityManager->remove($demande);
    $entityManager->flush();

    
    $this->addFlash('success', 'La demande a été supprimée avec succès.');

    
    return $this->redirectToRoute('app_demande_list');
}

}
