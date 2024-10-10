<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Component\Intl\Countries;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_SUPER_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access Denied. You do not have permission to access this area.');
        }
        $countries = Countries::getNames('fr');
        return [
            TextField::new('email', 'Email'), // Поле для email
            TextField::new('psuedo', 'Pseudo'), // Поле для email
            TextField::new('discord', 'Discord'), // Поле для email
            TextField::new('city', 'Ville'), // Поле для email
            ChoiceField::new('country', 'Country')
                ->setChoices([ // LES LISTES DES ROLES
                'Country' => array_flip($countries)
                ]),
            $avatar = ImageField::new('avatar')
                ->setUploadDir('public/divers/avatars')
                ->setBasePath('divers/avatars')
                ->setSortable(false)
                ->setFormTypeOption('required', false)->setColumns('col-md-8'),
            ChoiceField::new('roles', 'Roles')
                ->setChoices([ // LES LISTES DES ROLES
                        'Super Admin' => 'ROLE_SUPER_ADMIN',
                        'Admin' => 'ROLE_ADMIN',
                        'Moderator' => 'ROLE_MODO',
                        'Editor' => 'ROLE_EDITOR',
                    ])
                ->allowMultipleChoices(true)
                ->renderExpanded(true) // Отображаем как список чекбоксов (можно убрать, если не нужно)
                    
        ];
    }
}
