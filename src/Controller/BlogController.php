<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/new', name: 'article_new')]
    public function newArticle(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setAuteur($this->getUser());
            $entityManager->persist($article); //AJOUT
            $entityManager->flush();

            return $this->redirectToRoute('app_blog'); //affichage
        }

        return $this->render('blog/article_form.html.twig', 
        [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{id}', name: 'article_show')]
    public function showArticle(Article $article): Response
    {
        return $this->render('blog/article_show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/{id}/edit', name: 'article_edit')]
    public function editArticle(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/article_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{id}/delete', name: 'article_delete', methods: ['POST'])]
    public function deleteArticle(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $article);

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blog');
    }




    

    #[Route('/article/{id}/comment', name: 'comment_new')]
    public function newComment(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $comment = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setArticle($article);
            $comment->setDateComm(new \DateTime());
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/comment_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/comment/{id}/edit', name: 'comment_edit')]
    public function editComment(Request $request, Commentaire $comment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('article_show', ['id' => $comment->getArticle()->getId()]);
        }

        return $this->render('blog/comment_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, Commentaire $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_show', ['id' => $comment->getArticle()->getId()]);
    }
} 

