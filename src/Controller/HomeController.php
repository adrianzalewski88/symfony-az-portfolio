<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        CategoryRepository $categoryRepository,
    ): Response {
        $categories = $categoryRepository->findAllForHomepage();

        return $this->render('home/index.html.twig', [
            'categories' => $categories,
        ]);
    }
}