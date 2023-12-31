<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Dompdf\Dompdf;
use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Security\Core\Security;


class ProfileController extends AbstractController
{
    /**
     * @Route("/indexProfile", name="profile_index")
     */
    public function index(ProfileRepository $profileRepository): Response

    {
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
                $newFilename = uniqid() . '.png';

                // Déplacez le fichier dans le répertoire 'public/images'
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $profile->setPassport($newFilename);

                // Sauvegardez le chemin complet de l'image dans l'entité Profile
                //  $profile->setPassport($this->getParameter('images_directory').'/'.$newFilename);
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
    public function editProfile(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $profile = $entityManager->getRepository(Profile::class)->find($id);
        $originalPassport = $profile->getPassport(); // Sauvegarde de l'image d'origine
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('passport')->getData();

            if ($imageFile) {
                // Générez un nom de fichier unique pour la nouvelle image
                $newFilename = uniqid() . '.png';

                // Déplacez le fichier dans le répertoire 'public/images'
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                // Mettez à jour le champ "passport" avec le nouveau nom de fichier
                $profile->setPassport($newFilename);
            } else {
                // Si aucun fichier n'a été téléchargé, conservez l'image d'origine
                $profile->setPassport($originalPassport);
            }

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

        // Vérifiez si le paramètre "delete" est présent dans la requête
        if ($request->query->get('delete')) {
            // Si oui, supprimez le profil
            $entityManager->remove($profile);
            $entityManager->flush();
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('Profile/profile_delete.html.twig', [
            'profile' => $profile,
        ]);
    }


    /**
     * @Route("/profileContrat/{id}", name="profileContrat")
     */
    public function Contrat(ProfileRepository $profileRepository, $id): Response

    {
        $profile = $profileRepository->find($id);
        $user = $profile->getUser();
        $dateActuelle = new DateTime();
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();

        $contratData = [
            'prestataire' => $firstname . ' ' . $lastname,
            'client' => 'Streamlink',
            'date' => $dateActuelle->format("d/m/Y"),
            'objet' => 'Objet du contrat à définir',
        ];
        return $this->render('Profile/Contrat.html.twig', [
            'contrat' => $contratData,
            'profile' => $profile
        ]);
    }

    /**
     * @Route("/generate-pdf/{id}", name="generate_pdf")
     */
    public function generatePdf(ProfileRepository $profileRepository, $id)
    {
        $profile = $profileRepository->find($id);
        $user = $profile->getUser();
        $dateActuelle = new DateTime();
        $firstname = $user->getFirstname();
        $lastname = $user->getLastname();

        $contratData = [
            'prestataire' => $firstname . ' ' . $lastname,
            'client' => 'Streamlink',
            'date' => $dateActuelle->format("d/m/Y"),
            'objet' => 'Objet du contrat à définir',
        ];

        $html = $this->renderView('Profile/Contrat.html.twig', [
            'contrat' => $contratData,
            'profile' => $profile
        ]);

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $response = new Response($pdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="contrat.pdf"');

        return $response;
    }


    /**
     * @Route("/mail/{id}", name="send_mail")
     */
    public function sendContractEmail(MailerInterface $mailer, ProfileRepository $profileRepository, Security $security, $id)
    {
        // Get the currently logged-in user
        $user_admin = $security->getUser();
        $profile = $profileRepository->find($id);
        $profiles = $profileRepository->findAll();
        $user = $profile->getUser();
        $to = $user->getEmail();
        // Generate PDF content
        $pdfContent = $this->generatePdf($profileRepository, $id)->getContent();

        // Create an Email instance
        $email = (new Email())
            ->from($user_admin->getEmail()) // Set the "from" address to the user's email
            ->to($to)
            ->subject('Contrat')
            ->text('Veuillez trouver ci-joint le contrat en PDF.');

        // Attach the PDF content to the email
        $email->attach($pdfContent, 'contrat.pdf', 'application/pdf');

        // Send the email
        $mailer->send($email);

        return $this->renderForm('Profile/index.html.twig', [
            'profiles' => $profiles,
        ]);
    }
}