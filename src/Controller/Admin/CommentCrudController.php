<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }
    public function configureFields(string $pageName): iterable
    {
        if (!$this->isGranted('ROLE_MODO')) {
            throw new AccessDeniedException('Access Denied. You do not have permission to access this area.');
        }
        return [
            TextField::new('content', 'Commentaire'),
            TextField::new('user', 'User'),
            AssociationField::new('post', 'Post'),
            DateTimeField::new('createdAt', 'Created At'),

        ];
    }
            
}
    

