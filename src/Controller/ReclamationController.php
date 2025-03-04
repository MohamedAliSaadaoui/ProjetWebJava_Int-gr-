<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, MailerInterface $mailer): Response
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

            $email = (new Email())
                ->from('hello@demomailtrap.co') // Ton email d'envoi
                ->to('libero1809@gmail.com') // Email du client
                ->subject('Réclamation enregistrée')
                ->html("<p>Bonjour {$reclamation->getUser()->getName()},</p>
                    <p>Votre réclamation a bien été enregistrée avec l'objet : <strong>{$reclamation->getObjet()}</strong>.</p>
                    <p>Nous reviendrons vers vous sous peu.</p>
                    <p>Cordialement, <br> L'équipe Support</p>");

            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
            }

            // Persist the reclamation entity
            $entityManager->persist($reclamation);
            $entityManager->flush();

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
