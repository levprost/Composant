<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Contact;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class DashboardController extends AbstractDashboardController
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $content = "you need to be an admin";
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('Access Denied. You do not have permission to access this area.');
        }
        
        if ($user->getOpption() !== 'yes') {
            return new Response($content, 403);
        }

        if ($this->isGranted('ROLE_EDITOR')) {
            return $this->render('admin/dashboard.html.twig');
        } else {
            return $this->redirectToRoute('app_post');
        }
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Final - Administration')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin');
    }

    public function configureMenuItems(): iterable
    {
       
        $content = "you need to be an admin";
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('Access Denied. You do not have permission to access this area.');
        }

        // Проверка на доступ по 'option'
        if ($user->getOpption() !== 'yes') {
            return new Response($content, 403);
        }
        // Переход на сайт
        yield MenuItem::linkToRoute('Aller sur le site', 'fas fa-arrow-left', 'app_post');

        // (ROLE_EDITOR)
        if ($this->isGranted('ROLE_EDITOR')) {
            yield MenuItem::section('Articles', 'fas fa-pencil-alt');
            yield MenuItem::subMenu('Crées les articles', 'fas fa-blog')->setSubItems([
                MenuItem::linkToCrud('Créer un article', 'fas fa-plus-circle', Post::class)->setAction(Crud::PAGE_NEW),
            ]);
        }

        // Пункт меню для модератора (ROLE_MODO)
        if ($this->isGranted('ROLE_MODO')) {
            yield MenuItem::section('Messages de Contact', 'fas fa-envelope');
            yield MenuItem::subMenu('Contact', 'fas fa-comment-dots')->setSubItems([
                MenuItem::linkToCrud('Voir les messages', 'fas fa-eye', Contact::class)
                    ->setController(ContactCrudController::class),
            ]);
            yield MenuItem::section('Commentaires', 'fas fa-comments');
            yield MenuItem::subMenu('Commentaires', 'fas fa-comments')->setSubItems([
                MenuItem::linkToCrud('Voir les commentaires', 'fas fa-eye', Comment::class),
            ]);
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Gestion des Articles', 'fas fa-edit');
            yield MenuItem::subMenu('Articles', 'fas fa-newspaper')->setSubItems([
                MenuItem::linkToCrud('Voir les articles', 'fas fa-eye', Post::class),
            ]);
            yield MenuItem::section('Rubriques', 'fas fa-book');
            yield MenuItem::subMenu('Rubriques', 'fas fa-book-open')->setSubItems([
                MenuItem::linkToCrud('Créer une rubrique', 'fas fa-plus', Rubrik::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Voir les rubriques', 'fas fa-eye', Rubrik::class),
            ]);
        }

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::section('Utilisateurs', 'fas fa-user');
            yield MenuItem::subMenu('Utilisateurs', 'fas fa-users')->setSubItems([
                MenuItem::linkToCrud('Créer un utilisateur', 'fas fa-user-plus', User::class)->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Voir les utilisateurs', 'fas fa-eye', User::class),
            ]);
        }
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        if (!$user instanceof User) {
            throw new \Exception('Wrong user');
        }
        $avatar = 'divers/avatars/' . $user->getAvatar();

        return parent::configureUserMenu($user)
            ->setAvatarUrl($avatar)
            ->displayUserName(true)
            ->addMenuItems([
                MenuItem::linkToRoute('Mon profil', 'fas fa-user', 'user_profile', ['id' => $user->getId()]),
                MenuItem::linkToLogout('Déconnexion', 'fas fa-sign-out-alt')
            ]);
    }
}

