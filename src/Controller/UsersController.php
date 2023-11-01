<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserType;
//#[Route('/admin', name: 'admin_users_')]
class UsersController extends AbstractController
{
    #[Route('/index', name: 'admin_index')]
    public function index(UserRepository $usersRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $usersRepository->findBy([], ['firstname' => 'asc']);
        return $this->render('admin/users/index.html.twig', compact('users'));
    }

    #[Route('/user/add', name: 'user_add')]
    public function addUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User(); // Assuming you have an entity named User

        $form = $this->createForm(UserType::class, $user); // Create a form for user input
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index'); // Redirect to the user list page
        }

        return $this->render('admin/users/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/editUser/{id}", name="user_edit", methods={"GET", "POST"})
     */

    public function editUser(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $user=$entityManager->getRepository(User::class)->find($id);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_index'); // Redirect to the user list page
        }

        return $this->render('admin/users/edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/user/deleteUser/{id}", name="user_delete")
     */
    public function deleteUser(Request $request, EntityManagerInterface $entityManager,$id): Response
    {


        $user= $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('user non trouvé.');
        }

        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute('admin_index', [], Response::HTTP_SEE_OTHER);
    }

}