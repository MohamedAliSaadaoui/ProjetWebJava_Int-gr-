<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\Organisation;

use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OrganisationRepository; // Assurez-vous d'importer le repository d'Organisation
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/demande')]
final class DemandeController extends AbstractController
{
    #[Route(name: 'app_demande_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,OrganisationRepository $organisationRepository, int $id): Response
    {

        $demande = new Demande();

        // Récupérer l'organisation par son ID
        $organisation = $organisationRepository->find($id);
    
        // Vérifiez si l'organisation existe
        if (!$organisation) {
            throw $this->createNotFoundException('Organisation not found.');
        }
    
        // Associer l'organisation à la demande
        $demande->setOrganisation($organisation);
    
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($demande);
            $entityManager->flush();

            return $this->redirectToRoute('app_organisation_client', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande/new.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        return $this->render('demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/{id}/edit', name: 'demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_organisation_client', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
    }
        
     #[Route("/organisation/{organisationId}/demandes", name:"app_demande_liste")]
    public function listeDemandes($organisationId, DemandeRepository $demandeRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'organisation par son ID
        $organisation = $entityManager
            ->getRepository(Organisation::class)
            ->find($organisationId);

        // Si l'organisation n'existe pas, rediriger vers la page des organisations
        if (!$organisation) {
            throw $this->createNotFoundException('Organisation non trouvée');
        }

        // Récupérer les demandes associées à l'organisation
        $demandes = $demandeRepository->findBy(['organisation' => $organisation]);

        // Passer les données à la vue
        return $this->render('demande/list.html.twig', [
            'organisation' => $organisation,
            'demandes' => $demandes,
        ]);
    }

    #[Route("/demandes/organisation/{organisationId}", name:"demande_liste")]
    public function listeDemandesadmin($organisationId, DemandeRepository $demandeRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'organisation par son ID
        $organisation = $entityManager
            ->getRepository(Organisation::class)
            ->find($organisationId);

        // Si l'organisation n'existe pas, rediriger vers la page des organisations
        if (!$organisation) {
            throw $this->createNotFoundException('Organisation non trouvée');
        }

        // Récupérer les demandes associées à l'organisation
        $demandes = $demandeRepository->findBy(['organisation' => $organisation]);

        // Passer les données à la vue
        return $this->render('demande/listadmin.html.twig', [
            'organisation' => $organisation,
            'demandes' => $demandes,
        ]);
    }
    #[Route('/stat/stati', name: 'stat')]
public function stat(DemandeRepository $demandeRepository): Response
{
    // Récupérer le nombre de demandes par statut
    $statistiques = $demandeRepository->countDemandesByStatut();

    return $this->render('stat/statistiques.html.twig', [
        'statistiques' => $statistiques,
    ]);
}



  #[Route("/demande/traiter", name:"demande_traiter", methods:["POST"])]

public function traiterDemande(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!isset($data['id'])) {
        return new JsonResponse(['success' => false, 'message' => 'ID manquant'], 400);
    }

    $demande = $entityManager->getRepository(Demande::class)->find($data['id']);

    if (!$demande) {
        return new JsonResponse(['success' => false, 'message' => 'Demande non trouvée'], 404);
    }

    // Modifier le statut de la demande
    $demande->setStatut('Terminé');
    $entityManager->flush();

    return new JsonResponse(['success' => true]);
}

}
