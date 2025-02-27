<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
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
    public function index(ReponseRepository $reponseRepository , ReclamationRepository $reclamationRepository): Response
    {

        $mostReportedCategory = $reclamationRepository->getMostReportedCategory();
        $resolvedCount = $reclamationRepository->countResolved();
        $inProgressCount = $reclamationRepository->countInProgress();
        $topUser = $reclamationRepository->getTopUser();
        $peakDate = $reclamationRepository->getPeakComplaintDate();


        return $this->render('admin_dash_board/admindashbord.html.twig', [
            'responses' => $reponseRepository->findAll(),
            'reclamations' => $reclamationRepository->findAll(),
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
        ReclamationRepository $reclamationRepository, MailerInterface $mailer // Inject the repository
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

            $email = (new Email())
                ->from('shyhebboudaya@gmail.com') // Ton email d'envoi
                ->to('libero1809@gmail.com') // Email du client
                ->subject('Réclamation résolue!')
                ->html("<p>Bonjour {$reclamation->getUser()->getName()},</p>
                    <p>Votre réclamation a bien été répondu : <strong>{$reclamation->getObjet()}</strong>.</p>
                    <p>Vous pouvez la consulter immédiatement.</p>
                    <p>Cordialement, <br> L'équipe Support</p>");

            $mailer->send($email);

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
