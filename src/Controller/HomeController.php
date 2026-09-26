<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return new Response(
            '<h1>Hello World!</h1><p>GitHub Actions are all set!</p>'
        );
    }
}