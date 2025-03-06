<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    #[Route('/mailer', name: 'app_mailer')]
    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('rewear@noreply.com')
            ->to('nebilylynda@gmail.com')
            ->subject('Nouvelle réponse à votre question')
            ->html($this->getEmailContent());

        // Envoyer l'e-mail
        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {

        }

        return new Response('E-mail envoyé avec succès !');
    }

    /**
     * Génère le contenu HTML de l'e-mail.
     */
    private function getEmailContent(): string
    {
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
                        <p>Vous avez reçu une nouvelle réponse à votre question. Nous vous invitons à consulter la section "Questions" pour en savoir plus.</p>
                        <a href="http://votre-site.com/questions" class="button">Voir la réponse</a>
                    </div>
                    <div class="email-footer">
                        <p>Cet e-mail a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ';
    }
}
