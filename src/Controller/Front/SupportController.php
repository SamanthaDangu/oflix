<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    #[Route('/support', name: 'support', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('front/support/index.html.twig', [
            'controller_name' => 'SupportController',
        ]);
    }
}
