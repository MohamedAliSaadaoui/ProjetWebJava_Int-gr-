<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class AdminDashBoardController extends AbstractController
{
    // Tableau de bord général
    #[Route('/admin/dash/board', name: 'app_admin_dash_board')]
    public function index(): Response
    {
        return $this->render('admin_dash_board/admindashbord.html.twig', [
            'controller_name' => 'AdminDashBoardController',
        ]);
    }

    // Gestion des utilisateurs avec recherche
    #[Route('/admin/users', name: 'admin_users')]
    public function manageUsers(UserRepository $userRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');

        $users = $search
            ? $userRepository->createQueryBuilder('u')
                ->where('u.username LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult()
            : $userRepository->findAll();

        return $this->render('admin_dash_board/admindashbord.html.twig', [
            'users' => $users,
            'search' => $search,
        ]);
    }
}
