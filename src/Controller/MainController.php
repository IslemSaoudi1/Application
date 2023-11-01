<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(UserRepository $usersRepository): Response
    {
        $users = $usersRepository->findAll();
        if ($this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_USER')) {
            return $this->render('admin/users/index.html.twig', [
                'users' => $users,
            ]);
        } else {
            return $this->render('main/index.html.twig', [
                'controller_name' => 'MainController',
            ]);
        }
    }
}
