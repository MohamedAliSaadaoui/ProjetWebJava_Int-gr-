<?php

namespace App\Service;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailService
{
    public function sendEmail(string $to, string $subject, string $body): void
    {
        $transport = Transport::fromDsn('smtp://94f26bfc1511ef:ccf4d76458ef8d@sandbox.smtp.mailtrap.io:2525');
        $mailer = new Mailer($transport);
            $email = (new Email())
                ->from("no-reply@rewear.tn")
                ->to($to)
                ->priority(Email::PRIORITY_HIGH)
                ->subject($subject)
                ->html($body);

            $mailer->send($email);

    }
}