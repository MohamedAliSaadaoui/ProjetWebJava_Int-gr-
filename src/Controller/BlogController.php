<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class BlogController extends AbstractController
{
    // Page d'index avec pagination (3 articles par page)
    #[Route('/blog', name: 'app_blog')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = (int) $request->query->get('page', 1); // Numéro de page, défaut à 1
        $limite = 3; // 3 articles par page
        $decalage = ($page - 1) * $limite; // Calcul du décalage

        $repositoryArticle = $entityManager->getRepository(Article::class);
        $totalArticles = $repositoryArticle->count([]); // Nombre total d'articles
        $totalPages = (int) ceil($totalArticles / $limite); // Nombre total de pages

        // Récupérer les articles paginés
        $articles = $repositoryArticle->findBy(
            [], // Pas de critère spécifique
            ['date' => 'DESC'], // Tri par date décroissante (plus récent en premier)
            $limite, // Limite à 3 articles
            $decalage // Décalage pour la page actuelle
        );

        // Vérifier si la page demandée est valide
        if ($decalage >= $totalArticles || $page < 1) {
            $this->addFlash('warning', 'Page non trouvée.');
            return $this->redirectToRoute('app_blog'); // Redirection vers la première page
        }

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'pageActuelle' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    // Endpoint API pour les articles paginés
    #[Route('/api/blog/articles', name: 'api_articles', methods: ['GET'])]
    public function apiArticles(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = (int) $request->query->get('page', 1); // Numéro de page
        $limite = 3; // 3 articles par page
        $decalage = ($page - 1) * $limite;

        $repositoryArticle = $entityManager->getRepository(Article::class);
        $totalArticles = $repositoryArticle->count([]);
        $totalPages = (int) ceil($totalArticles / $limite);

        if ($decalage >= $totalArticles || $page < 1) {
            return $this->json(['message' => 'Page non trouvée'], 404);
        }

        $articles = $repositoryArticle->findBy(
            [],
            ['date' => 'DESC'],
            $limite,
            $decalage
        );

        // Préparer les données pour la réponse JSON
        $donnees = [
            'page' => $page,
            'limite' => $limite,
            'totalArticles' => $totalArticles,
            'totalPages' => $totalPages,
            'articles' => array_map(function (Article $article) {
                return [
                    'id' => $article->getId(),
                    'titre' => $article->getTitle(),
                    'contenu' => $article->getContent(),
                    'image' => $article->getImage(),
                    'date' => $article->getDate()->format('Y-m-d H:i:s'),
                ];
            }, $articles),
        ];

        return $this->json($donnees);
    }

    #[Route('/blog/new', name: 'article_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $article->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }
            
            $article->setDate(new \DateTime()); // Ajouter la date de création
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès.');
            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/article_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/blog/article/{id}', name: 'article_details')]
    public function articleDetails(
        Article $article,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $commentaire = new Commentaire();
        $commentaireForm = $this->createForm(CommentaireType::class, $commentaire);
        $commentaireForm->handleRequest($request);

        if ($commentaireForm->isSubmitted() && $commentaireForm->isValid()) {
            $commentaire->setArticle($article);
            $commentaire->setDateComm(new \DateTime());
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            return $this->redirectToRoute('article_details', ['id' => $article->getId()]);
        }

        return $this->render('blog/article_details.html.twig', [
            'article' => $article,
            'commentaireForm' => $commentaireForm->createView(),
            'commentaires' => $article->getCommentaires(),
        ]);
    }

    #[Route('/blog/edit/{id}', name: 'article_edit')]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Article modifié avec succès.');
            return $this->redirectToRoute('article_details', ['id' => $article->getId()]);
        }

        return $this->render('blog/article_edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/blog/delete/{id}', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        return $this->redirectToRoute('app_blog');
    }

    #[Route('/blog/liste', name: 'app_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();
        return $this->render('blog/liste_article.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/blog/delete_comment/{id}', name: 'comment_delete', methods: ['POST'])]
    public function supprimerComment(int $id, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $entityManager->getRepository(Commentaire::class)->find($id);
        if (!$commentaire) {
            throw $this->createNotFoundException('Commentaire non trouvé.');
        }

        $articleId = $commentaire->getArticle()->getId();
        $entityManager->remove($commentaire);
        $entityManager->flush();
        $this->addFlash('success', 'Commentaire supprimé avec succès.');

        return $this->redirectToRoute('article_details', ['id' => $articleId]);
    }

    #[Route('/blog/commentaire_edit/{id}/edit', name: 'commentaire_edit')]
    public function modifierCommentaire(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire modifié avec succès.');
            return $this->redirectToRoute('article_details', ['id' => $commentaire->getArticle()->getId()]);
        }

        return $this->render('blog/edit_comment.html.twig', [
            'form' => $form->createView(),
            'commentaire' => $commentaire,
        ]);
    }
}