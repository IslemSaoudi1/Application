<?php

namespace App\Controller;

use App\Form\ContactType;
//use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="app_contact")
     *
     */
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $address = $data->getEmail();
            $message = $data->getMessage();

            $email = (new Email())
                ->from($address)
                ->to('i.saoudi@streamlink.fr')
                ->subject('Demande de contact')
                ->text($message);

            $mailer->send($email);

            // Rediriger vers main.html.twig après l'envoi du courriel
            return $this->redirectToRoute('main');
        }

        return $this->renderForm('contact/index.html.twig', [
            'controller_name' => 'ContactController',
            'formulaire' => $form
        ]);
    }
}
