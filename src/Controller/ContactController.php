<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $contactFile = $form->get('image')->getData();
            if($contactFile){
                $newFilename = uniqid(). '-' .$contactFile->getExtension();
                $contactFile->move(
                    $this->getParameter('kernel.project_dir'). '/public/divers/contactImage',
                    $newFilename
                );
            }
            // Создаем объект ContactMessage и заполняем его данными из формы
            $contactMessage = new Contact();
            $contactMessage->setTitle($formData['title']);
            $contactMessage->setSujet($formData['sujet']);
            $contactMessage->setEmail($formData['email']);
            $contactMessage->setMessage($formData['message']);
            $contactMessage->setCreatedAt(new \DateTimeImmutable());

            // Сохраняем сообщение в базу данных
            $entityManager->persist($contactMessage);
            $entityManager->flush();

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}