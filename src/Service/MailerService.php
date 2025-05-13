<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendResetPasswordEmail($email)
    {
        $emailMessage = (new Email())
            ->from('noreply@tonapplication.com')
            ->to($email)
            ->subject('Réinitialisation de votre mot de passe')
            ->text('Cliquez ici pour réinitialiser votre mot de passe : <lien_de_reinitialisation>')
            // Optionnel : HTML message
            ->html('<p>Cliquez <a href="<lien_de_reinitialisation>">ici</a> pour réinitialiser votre mot de passe.</p>');

        $this->mailer->send($emailMessage);
    }
}
