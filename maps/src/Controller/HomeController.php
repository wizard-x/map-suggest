<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
*/
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request): Response
    {
        return $this->render('home/index.html.twig', ['yandex_api_key' => getenv('YANDEX_MAPS_API_TOKEN') ]);
    }
}
