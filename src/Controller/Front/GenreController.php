<?php

namespace App\Controller\Front;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenreController extends AbstractController
{
    #[Route('/genres', name: 'genre_list', methods: ['GET'])]
    public function list(GenreRepository $genreRepository): Response
    {
        return $this->render('front/genre/list.html.twig', [
            'genres' => $genreRepository->findAllWithMovies(),
        ]);
    }

    /**
     * @link https://symfony.com/doc/current/routing.html#converting-parameters-into-objects-param-converters
     */
    #[Route('/genre/{slug}', name: 'genre', methods: ['GET'])]
    public function show(Genre $genre): Response
    {
        // comme le nom du parametre de route ({slug}) correspond a une propriete
        // de l'entity Genre, le framework va automatiquement faire un findOneBy(['slug' => ...])

        return $this->render('front/genre/index.html.twig', [
            'genre' => $genre
        ]);
    }
}
