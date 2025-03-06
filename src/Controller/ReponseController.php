<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/reponse')]
final class ReponseController extends AbstractController
{
    #[Route('/', name: 'app_reponse_index', methods: ['GET'])]
    public function index(Request $request,ReponseRepository $reponseRepository , ReclamationRepository $reclamationRepository): Response
    {

        $search = $request->query->get('search'); // Nom d'utilisateur
        $sort = $request->query->get('sort'); // Tri
        $status = $request->query->all('status'); // Tableau des statuts cochés
        $date = $request->query->get('date'); // Date unique sélectionnée

        $reclamation = $reclamationRepository->findByFilters($search, $sort, $status, $date);

        $mostReportedCategory = $reclamationRepository->getMostReportedCategory();
        $resolvedCount = $reclamationRepository->countResolved();
        $inProgressCount = $reclamationRepository->countInProgress();
        $topUser = $reclamationRepository->getTopUser();
        $peakDate = $reclamationRepository->getPeakComplaintDate();


        return $this->render('reponse/admindashbord.html.twig', [
            'responses' => $reponseRepository->findAll(),
            'reclamations' => $reclamation,
            'mostReportedCategory' => $mostReportedCategory,
            'resolvedCount' => $resolvedCount,
            'inProgressCount' => $inProgressCount,
            'topUser' => $topUser,
            'peakDate' => $peakDate,
        ]);
    }

    #[Route('/new', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function createReponse(
        Request $request,
        EntityManagerInterface $entityManager,
        ReclamationRepository $reclamationRepository, MailService $mailService
    ): Response {

        $idReclamation = $request->query->get('id_reclamation'); // Récupérer l'ID depuis l'URL

        $reclamation = $reclamationRepository->findById($idReclamation);

        // Create a new response object
        $reponse = new Reponse();

        $reponse->setDateReponse(new \DateTime());
        $reponse->setReclamation($reclamation);

        // Create the form and pass reclamations to the form options
        $form = $this->createForm(ReponseType::class, $reponse, [
            'id_reclamation' => $idReclamation,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {



            $mailService->sendEmail('shyhebboudaya@gmail.com','Réclamation résolue!',"<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;'>
                <div style='text-align: center; padding-bottom: 20px;'>
                    <h2 style='color: #28a745;'>Votre réclamation a été résolue ! 🎉</h2>
                    <p style='color: #555;'>Bonjour <strong>{$reclamation->getUser()->getName()}</strong>,</p>
                </div>

                <div style='background: white; padding: 15px; border-radius: 5px;'>
                    <p><strong>Objet :</strong> {$reclamation->getObjet()}</p>
                    <p><strong>Description :</strong> {$reclamation->getDescription()}</p>
                    <p><strong>Date :</strong> " . $reclamation->getDateReclamation()->format('d/m/Y H:i') . "</p>
                    <p><strong>Statut :</strong> <span style='color: green;'>Résolue</span></p>
                    <p><strong>Réponse :</strong> {$reponse->getReponse()}</p>
                </div>

                <div style='text-align: center; margin-top: 20px;'>
                    <a href='https://mon-site.com/reclamations/{$reclamation->getId()}' 
                       style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Voir les détails
                    </a>
                </div>

                <div style='margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; text-align: center; color: #777; font-size: 12px;'>
                    <p>&copy; " . date('Y') . " MonSite - Nous restons à votre disposition !</p>
                </div>
            </div>");





            $reclamation->setStatus('Résolue');
            $entityManager->persist($reponse);
            $entityManager->flush();

            // Redirect to the reclamation show page or index
            return $this->redirectToRoute('app_reponse_index');
        }

        return $this->render('reponse/new.html.twig', [
            'form' => $form->createView(),
            'id_reclamation' => $idReclamation,
        ]);
    }


    #[Route('/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse , [
            'id_reclamation' => $reponse->getReclamation()->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
    }
}
