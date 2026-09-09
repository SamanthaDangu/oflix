<?php

namespace App\Controller\Front;

use App\Entity\Genre;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenreController extends AbstractController
{
    /**
     * @link https://symfony.com/doc/current/routing.html#converting-parameters-into-objects-param-converters
     */
    #[Route('/genre/{id}', name: 'genre', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Genre $genre): Response
    {
        // comme je demande un Genre, et qu'il y a un {id} dans la route
        // Le framework va automatiquement faire un find avec l'id

        return $this->render('front/genre/index.html.twig', [
            'genre' => $genre
        ]);
    }
}
