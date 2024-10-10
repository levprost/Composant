<?php

namespace App\Controller\Admin;

use App\Entity\Rubrik;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RubrikCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rubrik::class;
    }

  
    public function configureFields(string $pageName): iterable
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Access Denied. You do not have permission to access this area.');
        }
        return [
            TextField::new('name'),
        ];
    }
}
