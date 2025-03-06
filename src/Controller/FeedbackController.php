<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Question;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use App\Service\BadWordsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


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
    public function new(
        Request $request,
        Question $question,
        EntityManagerInterface $entityManager,
        BadWordsService $badWordsService,
        MailerInterface $mailer

    ): Response {
        // Récupérer les données du formulaire
        $feedback_text = $request->request->get('feedback_text');
        $user_name = $request->request->get('user_name');

        // Vérifier la présence de mots inappropriés
        $badWordsCheck = $badWordsService->containsBadWords($feedback_text);

        if ($badWordsCheck['containsBadWords']) {
            // Si des mots inappropriés sont détectés, renvoyer une erreur
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Votre réponse contient un langage inapproprié. Veuillez reformuler votre message de manière respectueuse.',
                    'badWordsCount' => count($badWordsCheck['badWords'])
                ]);
            }

            // Si ce n'est pas AJAX, rediriger avec un message flash
            $this->addFlash('error', 'Votre réponse contient un langage inapproprié. Veuillez reformuler votre message de manière respectueuse.');
            return $this->redirectToRoute('app_question_index');
        }

        // Créer un nouveau feedback
        $feedback = new Feedback();
        $feedback->setQuestion($question);
        $feedback->setAnsweredAt(new \DateTime());
        $feedback->setFeedbackText($feedback_text);
        $feedback->setUserName($user_name);
        $feedback->setApproved(0);

        // Enregistrer le feedback
        $entityManager->persist($feedback);
        $entityManager->flush();

        try {
            $email = (new Email())
                ->from('noreply@demomailtrap.co')
                ->to('nebilylynda@gmail.com') // Vérifiez que cette méthode existe
                ->subject('Nouvelle réponse à votre question')
                ->html($this->getEmailContent($feedback, $question));

            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }

        // Si c'est une requête AJAX, renvoyer une réponse JSON
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Votre réponse a été ajoutée avec succès',
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
        $this->addFlash('success', 'Votre réponse a été ajoutée avec succès');
        return $this->redirectToRoute('app_question_index');


    }

    private function getEmailContent(Feedback $feedback, Question $question): string
    {
        // Vous pouvez personnaliser ce template pour inclure les détails du feedback
        return '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Nouvelle réponse à votre question</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                }
                .email-header {
                    background-color: #007bff;
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .email-body {
                    padding: 20px;
                    color: #333333;
                }
                .email-body p {
                    line-height: 1.6;
                }
                .email-footer {
                    background-color: #f4f4f4;
                    padding: 10px;
                    text-align: center;
                    font-size: 12px;
                    color: #666666;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    margin-top: 20px;
                    background-color: #007bff;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 5px;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>Nouvelle réponse à votre question</h1>
                </div>
                <div class="email-body">
                    <p>Bonjour,</p>
                    <p>Vous avez reçu une nouvelle réponse à votre question: "' . htmlspecialchars($question->getQuestionText() ?? 'Votre question') . '"</p>
                    <p>La réponse de ' . htmlspecialchars($feedback->getUserName()) . ':</p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-left: 3px solid #007bff;">
                    ' . nl2br(htmlspecialchars($feedback->getFeedbackText())) . '
                    </p>
                    <a href="http://votre-site.com/questions/' . $question->getIdQ() . '" class="button">Voir tous les détails</a>
                </div>
                <div class="email-footer">
                    <p>Cet e-mail a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
    ';
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
