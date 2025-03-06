<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Question;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/', name: 'app_feedback_index', methods: ['GET'])]
    public function index(FeedbackRepository $feedbackRepository): Response
    {
        return $this->render('feedback/index.html.twig', [
            'feedback' => $feedbackRepository->findAll(),
        ]);
    }

    #[Route('/new/{idQ}', name: 'app_feedback_new', methods: ['POST'])]
    public function new(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        // Lire le fichier de mots interdits
        $badWordsFilePath = $this->getParameter('kernel.project_dir') . '/public/bad-words/fr.txt';
        $badWords = file($badWordsFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Récupérer les données du formulaire
        $feedbackText = $request->request->get('feedback_text');
        $userName = $request->request->get('user_name');

        // Vérifier si la réponse contient des mots interdits
        foreach ($badWords as $badWord) {
            if (stripos($feedbackText, $badWord) !== false) {
                // Si c'est une requête AJAX, renvoyer une erreur
                if ($request->isXmlHttpRequest()) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Votre réponse contient des mots irrespectueux. Veuillez reformuler votre message.',
                    ]);
                }

                // Si ce n'est pas AJAX, rediriger avec un message flash
                $this->addFlash('error', 'Votre réponse contient des mots irrespectueux. Veuillez reformuler votre message.');
                return $this->redirectToRoute('app_question_show', ['idQ' => $question->getIdQ()]);
            }
        }

        // Créer un nouveau feedback
        $feedback = new Feedback();
        $feedback->setQuestion($question);
        $feedback->setAnsweredAt(new \DateTime());
        $feedback->setFeedbackText($feedbackText);
        $feedback->setUserName($userName);
        $feedback->setApproved(0);

        // Enregistrer le feedback
        $entityManager->persist($feedback);
        $entityManager->flush();

        // Si c'est une requête AJAX, renvoyer une réponse JSON
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Votre réponse a été ajoutée avec succès.',
                'feedback' => [
                    'id' => $feedback->getIdF(),
                    'text' => $feedback->getFeedbackText(),
                    'userName' => $feedback->getUserName(),
                    'date' => $feedback->getAnsweredAt()->format('d/m/Y H:i'),
                    'approved' => $feedback->getApproved(),
                ]
            ]);
        }

        // Si ce n'est pas AJAX, rediriger avec un message flash
        $this->addFlash('success', 'Votre réponse a été ajoutée avec succès.');
        return $this->redirectToRoute('app_question_index');
    }

    #[Route('/{idF}', name: 'app_feedback_show', methods: ['GET'])]
    public function show(Feedback $feedback): Response
    {
        return $this->render('feedback/show.html.twig', [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{idF}/edit', name: 'app_feedback_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('feedback/edit.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/{idF}', name: 'app_feedback_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$feedback->getIdF(), $request->request->get('_token'))) {
            $entityManager->remove($feedback);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_feedback_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{idF}/{action}', name: 'app_feedback_vote', methods: ['POST'])]
    public function vote(Request $request, Feedback $feedback, string $action, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifier si l'utilisateur a déjà voté (via session)
        $sessionKey = 'voted_feedback_' . $feedback->getIdF();
        if ($request->getSession()->has($sessionKey)) {
            return $this->json([
                'success' => false,
                'message' => 'Vous avez déjà voté pour cette réponse.',
            ], 400);
        }

        // Valider l'action
        if (!in_array($action, ['up', 'down'])) {
            return $this->json([
                'success' => false,
                'message' => 'Action de vote invalide.',
            ], 400);
        }

        try {
            // Appliquer le vote
            if ($action === 'up') {
                $feedback->incrementApproved();
            } else {
                $feedback->decrementApproved();
            }

            // Enregistrer les modifications
            $entityManager->flush();

            // Enregistrer le vote dans la session
            $request->getSession()->set($sessionKey, $action);

            return $this->json([
                'success' => true,
                'message' => 'Votre vote a été enregistré.',
                'approved' => $feedback->getApproved(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de l\'enregistrement du vote.',
            ], 500);
        }
    }

}
