<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Title'),
            TextField::new('sujet', 'Sujet'),
            TextField::new('email', 'Email'),
            TextareaField::new('message', 'Message')->onlyOnIndex(), // Показываем сообщение только в списке
            DateTimeField::new('createdAt', 'Created At'),
            ImageField::new('image')
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        if ($this->isGranted('ROLE_MODO')) {
            return $actions
                ->remove(Crud::PAGE_INDEX, Action::EDIT) // Удаляем возможность редактировать сообщения в списке
                ->remove(Crud::PAGE_DETAIL, Action::EDIT) // Удаляем кнопку "Редактировать" в деталях
                ->add(Crud::PAGE_INDEX, Action::DETAIL);  // Добавляем кнопку "Просмотреть" для просмотра деталей сообщения
                 // Модератор может удалять сообщения
        }

        // Полный доступ для администраторов
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, Action::EDIT);
            
    }
}
