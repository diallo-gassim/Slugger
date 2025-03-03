<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    // Cette annotation de route définit une route qui inclut un slug et un ID dans l'URL.
    // Les exigences spécifient que l'ID doit être un nombre (\d+) et le slug doit être composé de lettres minuscules, de chiffres et de tirets ([a-z0-9-]+).
    #[Route('/{slug}-{id}', name: 'app_article_show',requirements:['id'=>'\d+','slug'=>'[a-z0-9-]+'], methods: ['GET','POST'])]
    public function show(Article $article, $slug,SluggerInterface $slugger): Response
    {


        // si le slug n'est renseigner dans l'url, on le génère à partir du titre de l'article
        $expectedSlug = $article->getSlug() ?: $slugger->slug($article->getTitle())->lower();

        // Si les slugs ne correspondent pas, redirige vers la bonne URL.
        if ($slug !== $expectedSlug) {
            return $this->redirectToRoute('app_article_show', [
                'id' => $article->getId(),
                'slug' => $expectedSlug,
            ]);
        }

        // Si les slugs correspondent, rend la vue de l'article.
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);

        
        
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
