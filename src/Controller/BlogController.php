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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BlogController extends AbstractController
{

    //Page blog affiché 
    #[Route('/blog', name: 'app_blog')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
        ]);
    }


//affichage et creation du formulaire

#[Route('/blog/new', name: 'app_blog_new')]
public function create(Request $request, EntityManagerInterface $entityManager): Response
{
    // Créer une nouvelle instance de l'article
    $article = new Article();

    // Créer le formulaire en lien avec l'entité Article
    $form = $this->createForm(ArticleType::class, $article);

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        
        // Sauvegarder l'article dans la base de données
        $entityManager->persist($article);
        $entityManager->flush();

        // Ajouter un message flash pour indiquer le succès
        $this->addFlash('success', 'Article créé avec succès !');

        // Rediriger vers la page de liste des articles après succès
        return $this->redirectToRoute('app_blog');
    }

    // Renvoyer la vue avec le formulaire
    return $this->render('blog/article_new.html.twig', [
        'form' => $form->createView(),
    ]);
}

//MODIFICATION

#[Route('/blog/edit/{id}', name: 'article_edit')]
public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
       

        $entityManager->flush();
        $this->addFlash('success', 'Article modifié avec succès.');

        return $this->redirectToRoute('app_blog');
    }

    return $this->render('blog/article_new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    
//SUPPRESSION
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





#[Route('/blog/comm', name: 'article_comm')]
public function show(Article $article, Request $request, EntityManagerInterface $entityManager): Response
{
    // Crée un nouveau commentaire
    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commentaire->setArticle($article); // Lier le commentaire à l'article
        $entityManager->persist($commentaire);
        $entityManager->flush();

        return $this->redirectToRoute('article_comm', ['id' => $article->getId()]);
    }

    
    return $this->render('blog/article_comm.html.twig', [
        'article' => $article,
        'commentaireForm' => $form->createView(),
    ]);   
}


#[Route('/blog/details_article', name: 'article_details')]
public function details(EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Article::class)->findOneBy([], ['date' => 'DESC']);

    if (!$article) {
        throw $this->createNotFoundException('Aucun article disponible.');
    }

    $commentaire = new Commentaire();
    $form = $this->createForm(CommentaireType::class, $commentaire);

    return $this->render('blog/article_details.html.twig', [
        'article' => $article,
        'commentaireForm' => $form->createView(),
    ]);
}

} 


