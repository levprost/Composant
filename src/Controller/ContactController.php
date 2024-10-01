<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            // Создаем объект Contact и заполняем его данными из формы
            $contactMessage = new Contact();
            $contactMessage->setTitle($formData['title']);
            $contactMessage->setSujet($formData['sujet']);
            $contactMessage->setEmail($formData['email']);
            $contactMessage->setMessage($formData['message']);
            $contactMessage->setCreatedAt(new \DateTimeImmutable());

            // Обработка изображения
            $contactFile = $form->get('image')->getData();
            if ($contactFile) {
                // Генерация нового уникального имени файла
                $originalFilename = pathinfo($contactFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$contactFile->guessExtension();

                // Перемещение файла в директорию public/divers/contactImage
                try {
                    $contactFile->move(
                        $this->getParameter('kernel.project_dir').'/public/divers/contactImage',
                        $newFilename
                    );
                    
                    // Установка пути изображения в объект Contact
                    $contactMessage->setImage('/divers/contactImage/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Ошибка при загрузке файла. Пожалуйста, попробуйте позже.');
                    return $this->redirectToRoute('contact');
                }
            }

            // Сохраняем сообщение в базу данных
            $entityManager->persist($contactMessage);
            $entityManager->flush();

            // Добавление сообщения об успешной отправке
            $this->addFlash('success', 'Ваше сообщение было успешно отправлено.');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}
