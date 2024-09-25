<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Rubrik;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\PostRepository;
use App\Repository\RubrikRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    private $repo;
    private $emi;
    public function __construct(PostRepository $repo, EntityManagerInterface $emi)
    {
        $this->repo = $repo;
        $this->emi = $emi;
    }
    #[Route('/', name: 'app_post')]
    public function index(): Response
    {
        $posts = $this->repo->findBy(['title' => 'Qui sommes nous?']);

        return $this->render('post/index.html.twig',[
            'posts' => $posts, 
        ]);
    } 
        //GESTION DES RUBRIKS
        #[Route('/rubrik/rubrik/{id}', name: 'posts_by_rubrik')]
        public function postsByRubrik(Rubrik $rubrik, postRepository $postRepository, Request $req): Response
    {

        $post = $postRepository->findByRubrik($rubrik);
        return $this->render('rubrik/rubrik.html.twig', [
            'rubrik' => $rubrik,
            'post' => $post,
        ]);
    }
     #[IsGranted('ROLE_USER')]
    #[Route('/post/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function showone(Post $post, Request $req, $id, PostRepository $reppo, EntityManagerInterface $emi, CommentRepository $crepo): Response
    {
        // Vérification du post
        if (!$post) {
            return $this->redirectToRoute('app_post');
        }

        $comments = new Comment();
        $posts = $reppo->find($id);
        $postsFromSameRubrik = $reppo->findTwoPostsFromSameRubrik($post->getRubrik()->getId(), $post->getId());
        // Créer le formulaire
        $commentForm = $this->createForm(CommentFormType::class, $comments);
        $commentForm->handleRequest($req);
        // Traitement du formulaire de commentaire
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $user = $this->getUser();
            $comments->setUser($user);
            $comments->setPost($posts);
            $comments->setCreatedAt(new \DateTimeImmutable());

            // Persister le commentaire
            $emi->persist($comments);
            $emi->flush();
            // Rediriger pour éviter la resoumission du formulaire
            return $this->redirectToRoute('show', ['id' => $id]);
        }
        // Récupération des commentaires pour le post
        $allComments = $crepo->findByPostOrderedByCreatedAtDesc($id);

        // Rendre la vue avec les données appropriées
        return $this->render('show/show.html.twig', [
            'post' => $post,
            'posts2' => $postsFromSameRubrik,
            'comments' => $allComments,
            'comment_form' => $commentForm->createView()
        ]);
        
    }

}