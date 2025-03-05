<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;



#[Route('/reclamation')]
final class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository, UserRepository $userRepository): Response
    {
        // Get all the reclamations of the current user (if user is logged in)
        $user = $userRepository->findById(1); // Assuming you're using Symfony's security system to manage users
        $reclamations = $reclamationRepository->findByUser($user);

        return $this->render('user_dash_board/user_dash_board2.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository,MailService $mailService): Response
    {
        $reclamation = new Reclamation();

        $user = $userRepository->findById(1);  // Use the findById method from UserRepository
        if ($user) {
            $reclamation->setUser($user); // Set the user for the reclamation
        } else {
            // Handle the case where the user does not exist (optional)
            throw $this->createNotFoundException('User not found.');
        }

        $reclamation->setStatus('En Cours');
        $reclamation->setDateReclamation(new \DateTime());

        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'user_name' => $user ? $user->getName() : 'Guest',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle the file upload (if any)
            $attachments = $form->get('attachments')->getData();
            $attachmentUrls = [];

            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $newFilename = uniqid().'.'.$attachment->guessExtension();
                    $attachment->move(
                        $this->getParameter('photo_directory'),
                        $newFilename
                    );
                    $attachmentUrls[] = '/uploads/photos/'.$newFilename;
                }
                $reclamation->setAttachments(implode('; ', $attachmentUrls));
            }

            $reclamation->setDateReclamation(new \DateTime());

            // Persist the reclamation entity
            $entityManager->persist($reclamation);
            $entityManager->flush();


            $mailService->sendEmail('shyhebboudaya@gmail.com','Réclamation enregistrée!',"<div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;'>
            <div style='text-align: center; padding-bottom: 20px;'>
                <h2 style='color: #007bff;'>Votre réclamation a été enregistrée !</h2>
                <p style='color: #555;'>Bonjour <strong>{$reclamation->getUser()->getName()}</strong>,</p>
            </div>

            <div style='background: white; padding: 15px; border-radius: 5px;'>
                <p><strong>Objet :</strong> {$reclamation->getObjet()}</p>
                <p><strong>Description :</strong> {$reclamation->getDescription()}</p>
                <p><strong>Date :</strong> " . $reclamation->getDateReclamation()->format('d/m/Y H:i') . "</p>
                <p><strong>Statut :</strong> <span style='color: red;'>En cours</span></p>
            </div>

            <div style='text-align: center; margin-top: 20px;'>
                <a href='https://mon-site.com/reclamations/{$reclamation->getId()}'
                   style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       Voir ma réclamation
            </a>
            </div>

            <div style='margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; text-align: center; color: #777; font-size: 12px;'>
                <p>&copy; " . date('Y') . " ReWear - Tous droits réservés.</p>
            </div>
        </div>");

            return $this->redirectToRoute('app_reclamation_index');
        }

        return $this->render('contact_us/contact_us.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(int $id, ReclamationRepository $reclamationRepository): Response
    {
        // Fetch the reclamation by ID
        $reclamation = $reclamationRepository->find($id);

        // Handle the case if reclamation is not found
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found.');
        }

        // Render the template and pass the reclamation data
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'user_name'=>$reclamation->getUser()->getName(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle any file changes and updates
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}
