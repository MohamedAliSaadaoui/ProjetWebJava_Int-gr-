<?php

namespace App\Controller;
use Symfony\Component\String\Slugger\SluggerInterface;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BlogController extends AbstractController
{

    //Page blog 
    #[Route('/blog', name: 'app_blog')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }


//CREATION D'UN ARTICLE

#[Route('/blog/new', name: 'article_new')]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $article = new Article();
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'upload de l'image
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('uploads_directory'), // Chemin défini dans services.yaml
                    $newFilename
                );
                $article->setImage($newFilename);
            } catch (FileException $e) {
                // Gérer l'erreur si nécessaire
            }
        }
        $entityManager->persist($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_blog');
    }
    return $this->render('blog/article_new.html.twig', [
        'form' => $form->createView(),
    ]);
}
//MODIFICATION BLOG

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

    return $this->render('blog/article_new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    
//SUPPRESSION BLOG
#[Route('/blog/delete/{id}', name: 'article_delete', methods: ['POST'])]
public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
        $entityManager->remove($article);
        $entityManager->flush();
        $this->addFlash('success', 'Article supprimé avec succès.');
    }

    return $this->redirectToRoute('app_blog');
}



// Affichage de la liste des articles
#[Route('/blog/liste', name: 'app_liste')]
public function liste(EntityManagerInterface $entityManager): Response
{
    $articles = $entityManager->getRepository(Article::class)->findAll();

    // Rendu de la page avec la liste des articles
    return $this->render('blog/liste_article.html.twig', [
        'articles' => $articles, 
    ]);
}

// #[Route('/blog/details_article', name: 'article_details')]
// public function details(EntityManagerInterface $entityManager): Response
// {
//     $article = $entityManager->getRepository(Article::class)->findOneBy([], ['date' => 'DESC']);

//     if (!$article) {
//         throw $this->createNotFoundException('Aucun article disponible.');
//     }

//     $commentaire = new Commentaire();
//     $form = $this->createForm(CommentaireType::class, $commentaire);

//     return $this->render('blog/article_details.html.twig', [
//         'article' => $article,
//         'commentaireForm' => $form->createView(),
//     ]);
// }

//ARTICLE DETAILS
#[Route('/blog/article/{id}', name: 'article_details')]
public function detailsArticle(Article $article): Response
{
    return $this->render('blog/article_details.html.twig', [
        'article' => $article,
    ]);
}

//affichage et ajout de commentaire
#[Route('/blog/show_commentaire/{id}', name: 'article_A')]
public function showComment(EntityManagerInterface $entityManager, Request $request, int $id): Response
{
    // Récupérer l'article avec l'ID spécifié
    $article = $entityManager->getRepository(Article::class)->find($id);
    // Si l'article n'existe pas
    if (!$article) {
        throw $this->createNotFoundException('Aucun article trouvé.');}
    // Créer une nouvelle instance de commentaire
    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);
    // Vérifier si le formulaire de commentaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        $commentaire->setArticle($article); // Lier le commentaire à l'article
        $entityManager->persist($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire ajouté avec succès.');
        return $this->redirectToRoute('article_A', ['id' => $id]);
    }
    // Récupérer tous les commentaires associés à l'article
    $commentaires = $article->getCommentaires();

    // Renvoyer la vue avec l'article, le formulaire de commentaire et la liste des commentaires
    return $this->render('blog/article_details.html.twig', [
        'article' => $article,
        'commentaireForm' => $form->createView(),
        'commentaires' => $commentaires,
    ]);
}





}







