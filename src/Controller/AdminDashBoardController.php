<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashBoardController extends AbstractController
{
    #[Route('/admin/dash/board', name: 'app_admin_dash_board')]
    public function index(): Response
    {
        return $this->render('admin_dash_board/admindashbord.html.twig', [
            'controller_name' => 'AdminDashBoardController',
        ]);
    }
}


