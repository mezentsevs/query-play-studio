<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', priority: -10)]
    #[Route('/{path}', name: 'app_catch_all', requirements: ['path' => '^(?!(api|build|_profiler|_wdt)/).+'], priority: -20)]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }
}
