<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Article;
use App\Entity\PropertySearch;
use App\Entity\Category;
use App\Entity\CategorySearch;
use App\Entity\PriceSearch;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Form\PropertySearchType;
use App\Form\CategorySearchType;
use App\Form\PriceSearchType;

class IndexController extends AbstractController
{
    #[Route('/', name: 'article_list')]
    public function home(EntityManagerInterface $entityManager, Request $request): Response
    {
        $propertySearch = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $propertySearch);
        $form->handleRequest($request);

        $articles = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $propertySearch->getNom();
            if ($nom != "") {
                $articles = $entityManager->getRepository(Article::class)->findBy(['nom' => $nom]);
            } else {
                $articles = $entityManager->getRepository(Article::class)->findAll();
            }
        }

        return $this->render('articles/index.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }
       

    #[Route('/article/save')]
    public function save(EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $article->setNom('Article 3');
        $article->setPrix(2500);

        $entityManager->persist($article);
        $entityManager->flush();

        return new Response('Article enregistré avec ID '.$article->getId());
    }

    #[Route('/article/new', name: 'new_article', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class,$article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('article_list');
        }


        return $this->render('articles/new.html.twig',['form' => $form->createView()]);
    }

    #[Route('/article/{id}', name: 'article_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas.');
        }

        return $this->render('articles/show.html.twig', [
            'article' => $article
        ]);
    }

    #[Route('/article/edit/{id}', name: 'edit_article', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $article = $entityManager->getRepository(Article::class)->find($id);
        $form = $this->createForm(ArticleType::class,$article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/edit.html.twig', ['form' =>

        $form->createView()]);

    }

    #[Route('/article/delete/{id}', name: 'delete_article', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('L\'article demandé n\'existe pas.');
        }

        if (!$this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('article_list');
    }

    #[Route('/category/newCat', name: 'new_category', methods: ['GET', 'POST'])]
    public function newCategory(Request $request, EntityManagerInterface $entityManager) {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
        }
        return $this->render('articles/newCategory.html.twig',['form'=>
        $form->createView()]);
    }

    #[Route('/artcat', name: 'article_par_cat', methods: ['GET', 'POST'])]
    public function articlesParCategorie(Request $request, EntityManagerInterface $entityManager) {
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class,$categorySearch);
        $form->handleRequest($request);
        $articles= [];
        
        
        if($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();
            if ($category!="")
        
                $articles= $category->getArticles();
        
            else
                $articles= $this->$entityManager->getRepository(Article::class)->findAll();
        }
        return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),'articles' => $articles]);
    }


    #[Route('/articles/art_prix', name: 'articles_par_prix', methods: ['GET', 'POST'])]
    public function articlesParPrix(Request $request, EntityManagerInterface $entityManager): Response
    {
        $priceSearch = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class, $priceSearch);
        $form->handleRequest($request);

        $articles = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $minPrice = $priceSearch->getMinPrice();
            $maxPrice = $priceSearch->getMaxPrice();

            $articles = $entityManager->getRepository(Article::class)
                                    ->findByPriceRange($minPrice, $maxPrice);
        }

        return $this->render('articles/articlesParPrix.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }

}