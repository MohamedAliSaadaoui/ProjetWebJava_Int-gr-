<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class AdminDashBoardController extends AbstractController
{
    #[Route('/admin/dash/board', name: 'admin_users')]
public function index(UserRepository $userRepository, Request $request): Response
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
