<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/indexProfile", name="profile_index")
     */
    public function index(ProfileRepository $profileRepository): Response

    {
        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        $profiles = $profileRepository->findAll();

        return $this->render('Profile/index.html.twig', [
            'profiles' => $profiles,
        ]);
    }
    /**
     * @Route("/addProfile", name="profile_add")
     */
    public function addProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $profile = new Profile();
        $form = $this->createForm(ProfileType::class, $profile);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('passport')->getData();

            if ($imageFile) {
                // Générez un nom de fichier unique pour l'image
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Déplacez le fichier dans le répertoire 'public/images'
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                // Sauvegardez le chemin complet de l'image dans l'entité Profile
                $profile->setPassport($this->getParameter('images_directory').'/'.$newFilename);
            }

            $entityManager->persist($profile);
            $entityManager->flush();

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/add_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/editProfile/{id}", name="profile_edit", methods={"GET", "POST"})
     */
    public function editProfile(Request $request,EntityManagerInterface $entityManager, $id): Response
    {
        $profile = $entityManager->getRepository(Profile::class)->find($id);
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('profile_index');
        }
        return $this->render('profile/edit_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/deleteProfile/{id}", name="profile_delete")
     */
    public function deleteProfile(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $profile = $entityManager->getRepository(Profile::class)->find($id);

        if (!$profile) {
            throw $this->createNotFoundException('Profil non trouvé.');
        }

        $entityManager->remove($profile);
        $entityManager->flush();

        return $this->redirectToRoute('profile_index', [], Response::HTTP_SEE_OTHER);
    }


}