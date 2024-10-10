<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        if (!$this->isGranted('ROLE_EDITOR')) {
            throw new AccessDeniedException('Access Denied. You do not have permission to access this area.');
        }
        return [
            IntegerField::new('id')->onlyOnIndex(),
            TextField::new('title')->setColumns('col-md-6'),
            TextField::new('subtitle')->setColumns('col-md-6'),
            TextEditorField::new('content')->setColumns('col-md-12'),
            TextEditorField::new('content1')->setColumns('col-md-12'),
            TextEditorField::new('content2')->setColumns('col-md-12'),
            TextField::new('video')->setColumns('col-md-12'),

            $photo = ImageField::new('photo')
                ->setUploadDir('public/divers/images')
                ->setBasePath('divers/images')
                ->setSortable(false)
                ->setFormTypeOption('required', false)->setColumns('col-md-2'),
            $image = ImageField::new('image')
                ->setUploadDir('public/divers/images')
                ->setBasePath('divers/images')
                ->setSortable(false)
                ->setFormTypeOption('required', false)->setColumns('col-md-2'),
            $benchmark = ImageField::new('benchmark')
                ->setUploadDir('public/divers/images')
                ->setBasePath('divers/images')
                ->setSortable(false)
                ->setFormTypeOption('required', false)->setColumns('col-md-2'),
        

            AssociationField::new('rubrik')->setColumns('col-md-6'),
            AssociationField::new('user')->setColumns('col-md-6'),
            DateField::new('createdAt')->onlyOnIndex(),

            $isPublished = BooleanField::new('isPublished')->setColumns('col-md-1')->setLabel('Publié')
            ];
    }

    public function configureCreud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Post')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(6)
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add('title')
            ->add('rubrik')
            ->add('createdAt')
        ;
    }
    public function createEntity(string $entityFqcn)
    {
        $post = new Post();
        $post->setCreatedAt(new \DateTimeImmutable()); // Установка текущей даты при создании
        return $post;
    }
}
