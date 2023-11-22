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
use App\Form\TaskType;
use App\Entity\Task;
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
        $form = $this->createForm(TaskType::class);

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
    /**
     * @Route("/assign-task/{userId}", name="assign_task")
     */
    public function assignTask(Request $request, EntityManagerInterface $entityManager, int $userId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $task = new Task();
        $task->setAssignedUser($user);

        // Créer le formulaire d'ajout de tâche
        $taskForm = $this->createForm(TaskType::class, $task);

        // Traiter la soumission du formulaire
        $taskForm->handleRequest($request);

        if ($taskForm->isSubmitted() && $taskForm->isValid()) {
            // Persister la tâche en base de données
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Task assigned successfully.');

            return $this->redirectToRoute('admin_index');
        }

        // Rendre la vue avec le formulaire
        return $this->render('admin/users/Task.html.twig', [
            'taskForm' => $taskForm->createView(),
        ]);
    }
    /**
     * @Route("/user-tasks", name="user_tasks")
     */
    public function userTasks(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('login');
        }
        $tasks = $user->getTasks();

        return $this->render('admin/users/Listtasks.html.twig', [
            'tasks' => $tasks,
        ]);
    }
    /**
     * @Route("/update-percentage/{taskId}", name="update_percentage", methods={"POST"})
     */
    public function updatePercentage(Request $request, EntityManagerInterface $entityManager, int $taskId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $taskRepository = $entityManager->getRepository(Task::class);
        $task = $taskRepository->find($taskId);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $newPercentage = $request->request->get('newPercentage');

        // Check if the new percentage is provided
        if ($newPercentage !== null) {
            // Validate and update the percentage if necessary
            if (is_numeric($newPercentage) && $newPercentage >= 0 && $newPercentage <= 100) {
                $task->setPercentageComplete($newPercentage);
                $entityManager->flush();

                $this->addFlash('success', 'Task progress updated successfully.');
            } else {
                $this->addFlash('error', 'Invalid percentage value.');
            }
        }

        return $this->redirectToRoute('user_tasks');
    }


}