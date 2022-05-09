<?php

namespace App\Controller\Front;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GenreController extends AbstractController
{
    /**
     * @link https://symfony.com/bundles/SensioFrameworkExtraBundle/current/annotations/converters.html#1-fetch-automatically
     * 
     * @Route("/genre/{id}", name="genre", requirements={"id": "\d+"})
     */
    public function show(Genre $genre): Response
    {
        // comme je demande un Genre, et qu'il y a un {id} dans la route
        // Le framework va automatiquement faire un find avec l'id

        return $this->render('front/genre/index.html.twig', [
            'genre' => $genre
        ]);
    }

    /**
     * @link https://symfony.com/bundles/SensioFrameworkExtraBundle/current/annotations/converters.html#1-fetch-automatically
     * 
     * @Route("/genre/{name}", name="genre_deux")
     */

    public function showByName(Genre $genre): Response
    {
        // comme je demande un Genre, et qu'il y a un {name} dans la route
        // Le framework va automatiquement faire un find avec la propriété du même nom (name)

        return $this->render('front/genre/index.html.twig', [
            'genre'  => $genre
        ]);
    }

    /**
     * show all genres
     */
    public function showAll(GenreRepository $genre): Response
    {
        $genres = $genre->findAll();

        return $this->render('genre.html.twig', [
            'genre' => $genres
        ]);
    }
}
