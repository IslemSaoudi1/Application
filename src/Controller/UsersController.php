<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
    #[Route('/admin/add', name: 'admin_add')]
    public function addUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserRepository $usersRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $managers = $usersRepository->findManagers();
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['managers' => $managers]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $selectedRoles = $form->get('roles')->getData();
            $user->setRoles($selectedRoles);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/users/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/admin/editUser/{id}", name="admin_edit", methods={"GET", "POST"})
     */

    public function editUser(Request $request,UserRepository $usersRepository , EntityManagerInterface $entityManager, $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user=$entityManager->getRepository(User::class)->find($id);
        $managers = $usersRepository->findManagers();
        $form = $this->createForm(UserType::class, $user, ['managers' => $managers]);
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
     * @Route("/admin/deleteUser/{id}", name="admin_delete")
     * /
     */
    public function deleteUser(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Profil non trouvé.');
        }
        if ($request->query->get('delete')) {
            $entityManager->remove($user);
            $entityManager->flush();
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/users/delete_user.html.twig', [
            'user' => $user,
        ]);
    }
}