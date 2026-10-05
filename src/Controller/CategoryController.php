<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/categories')]
class CategoryController extends AbstractController
{
    #[Route('', name: 'dashboard_categories_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('dashboard/categories/index.html.twig', [
            'categories' => $categoryRepository->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'dashboard_categories_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Category created successfully.');

            return $this->redirectToRoute('dashboard_categories_index');
        }

        return $this->render('dashboard/categories/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard_categories_edit', methods: ['GET', 'POST'])]
    public function edit(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Category updated successfully.');

            return $this->redirectToRoute('dashboard_categories_index');
        }

        return $this->render('dashboard/categories/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'dashboard_categories_delete', methods: ['POST'])]
    public function delete(
        Category $category,
        EntityManagerInterface $entityManager
    ): Response {
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'Category deleted successfully.');

        return $this->redirectToRoute('dashboard_categories_index');
    }
}