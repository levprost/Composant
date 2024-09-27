<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Rubrik;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    protected $userRepository;

    //Mise en place du constructeur
    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //return parent::index();
        //DEFINIR LE ROLE MINIMUM POUR ACCEDER AU DASHBOARD
        if($this->isGranted('ROLE_EDITOR')){
            return $this->render('admin/dashboard.html.twig');
        }else
            return $this->redirectToRoute('app_post');

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Final - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Go To Site','fa-solid fa-arrow-rotate-left', 'app_post');
        if($this->isGranted('ROLE_ADMIN')){
            yield MenuItem::linkToDashboard('Dashboard', 'fa-home',)->setPermission('ROLE_SUPER_ADMIN_');
        }

        if($this->isGranted('ROLE_EDITOR')){
            yield MenuItem::section('Posts');
            yield MenuItem::subMenu('Creation de posts','fa-sharp fa-solid fa-blog')->setSubItems([
                MenuItem::linkToCrud('Create Posts', 'fas fa-newspaper', Post::class)->setAction(Crud::PAGE_NEW),
                
            ]);
        }
        if($this->isGranted('ROLE_MODO')){
            yield MenuItem::section('Comment');
            yield MenuItem::submenu('Comment','fa fa-comment-dots')->setSubItems([
                MenuItem::linkToCrud('Create comments','fas fa-newspaper', Comment::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show comments','fas fa-eye', Comment::class),
            ]);
        }

        if($this->isGranted('ROLE_ADMIN')){
            yield MenuItem::section('Editions de Posts');
            yield MenuItem::subMenu('posts','fa-sharp fa-solid fa-blog')->setSubItems([
                MenuItem::linkToCrud('Show Posts', 'fas fa-eye', Post::class)
            ]); 
            yield MenuItem::section('Rubrik');
            yield MenuItem::submenu('Rubriks','fa-solid-book-open-reader')->setSubItems([
                MenuItem::linkToCrud('Create Rubrik','fas fa-newspaper', Rubrik::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show Rubrik','fas fa-eye', Rubrik::class),
                ]);    
        }

        if($this->isGranted('ROLE_SUPER_ADMIN')){
            yield MenuItem::section('User');
            yield MenuItem::submenu('User','fa-user-circle')->setSubItems([
                MenuItem::linkToCrud('Create User','fa fa-plus-circle', User::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Show User','fas fa-eye', User::class),
                ]);
        }
    }
    public function configureUserMenu(UserInterface $user) : UserMenu 
    {
        if(!$user instanceof User){
            throw new \Exception('Wrong user');
        }
        $avatar = 'divers/avatars/' . $user->getAvatar();

        return parent::configureUserMenu($user)
            ->setAvatarUrl($avatar);
    }
}
