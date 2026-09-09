<?php

namespace App\Controller\Front;

use App\Entity\Genre;
use App\Entity\Movie;
use App\Models\JsonError;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    /**
     * Liste les films disponibles pour l'API publique.
     */
    #[Route('/api/movies', name: 'api_list_movies', methods: ['GET'])]
    public function listMovies(MovieRepository $movieRepository): Response
    {
        return $this->json(
            $movieRepository->findAll(),
            Response::HTTP_OK,
            [],
            ['groups' => ['list_movie']]
        );
    }

    /**
     * Liste les genres disponibles pour l'API publique.
     */
    #[Route('/api/genres', name: 'api_list_genres', methods: ['GET'])]
    public function listGenres(GenreRepository $genreRepository): Response
    {
        return $this->json(
            $genreRepository->findAll(),
            Response::HTTP_OK,
            [],
            ['groups' => 'list_genre']
        );
    }

    #[Route('/api/genres/{id}', name: 'api_genre', methods: ['GET'])]
    public function showGenre(?Genre $genre = null): Response
    {
        if ($genre === null) {
            $error = new JsonError(Response::HTTP_NOT_FOUND, Genre::class . ' non trouvé');
            return $this->json($error, $error->getError());
        }

        return $this->json(
            $genre,
            Response::HTTP_OK,
            [],
            ['groups' => 'show_genre']
        );
    }

    /**
     * Liste les films associes a un genre.
     */
    #[Route('/api/genres/{id}/movies', name: 'api_genre_movies', methods: ['GET'])]
    public function showMoviesFromGenre(?Genre $genre = null): Response
    {
        if ($genre === null) {
            $error = new JsonError(Response::HTTP_NOT_FOUND, 'Genre non trouvé');
            return $this->json($error, $error->getError());
        }

        return $this->json(
            $genre->getMovies(),
            Response::HTTP_OK,
            [],
            ['groups' => ['list_movie']]
        );
    }

    /**
     * Cree un film depuis un payload JSON et renvoie sa representation detaillee.
     *
     * @link https://symfony.com/doc/current/validation.html#using-the-validator-service
     */
    #[Route('/api/movies', name: 'api_movies_create', methods: ['POST'])]
    public function createMovie(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        try {
            // slug et updatedAt sont geres en interne (MovieListener) : on les ignore
            // pour eviter qu'un appelant ne les impose directement via l'API
            $newMovie =  $serializer->deserialize($data, Movie::class, 'json', [
                'ignored_attributes' => ['slug', 'updatedAt'],
            ]);
        } catch (Exception $e) {
            return new JsonResponse("Hoouuu !! Ce qui vient d'arriver est de votre faute : JSON invalide", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $errors = $validator->validate($newMovie);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse($errorsString, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $doctrine->persist($newMovie);
        $doctrine->flush();

        return $this->json(
            $newMovie,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['show_movie']]
        );
    }

    /**
     * Cree un genre via l'API securisee.
     *
     * @link https://symfony.com/doc/current/validation.html#using-the-validator-service
     */
    #[Route('/api/secure/genres', name: 'api_genres_create', methods: ['POST'])]
    public function createGenre(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        try {
            $newgenre =  $serializer->deserialize($data, Genre::class, 'json');
        } catch (Exception $e) {

            return new JsonResponse("Hoouuu !! Ce qui vient d'arriver est de votre faute : JSON invalide", Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $errors = $validator->validate($newgenre);
        if (count($errors) > 0) {
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);

            return $this->json($myJsonError, $myJsonError->getError());
        }

        $doctrine->persist($newgenre);
        $doctrine->flush();

        return $this->json(
            $newgenre,
            Response::HTTP_CREATED,
            [],
            ['groups' => 'show_genre']
        );
    }
}
