<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactUsController extends AbstractController
{
    #[Route('/contact.html', name: 'app_contact_us')]
    public function index(): Response
    {
        return $this->render('contact_us/contact_us.html.twig', [
            'controller_name' => 'ContactUsController',
        ]);
    }
}
