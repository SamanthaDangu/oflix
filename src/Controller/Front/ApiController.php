<?php

namespace App\Controller\Front;

use App\Entity\Genre;
use App\Entity\Movie;
use App\Models\JsonError;
use App\Models\Movies;
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
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/movies", name="api_list_movies", methods={"GET"})
     */
    public function listMovies(MovieRepository $movieRepository): Response
    {
        // on renvoit une réponse de type JsonResponse
        // c'est la même chose que Response, en plus spécifique
        // car ça rajoute le contentType dans les headers
        return $this->json(
            // les données à transformer en JSON
            $movieRepository->findAll(),
            // HTTP STATUS CODE
            200,
            // HTTP headers supplémentaires, dans notre cas : aucune
            [],
            // Contexte de serialisation, les groups de propriété que l'on veux serialise
            ['groups'=> ['list_movie']]
        );
    }

    /**
     * @Route("/api/genres", name="api_list_genres", methods={"GET"})
     */
    public function listGenres(GenreRepository $genreRepository): Response
    {
        // on renvoit une réponse de type JsonResponse
        // c'est la même chose que Response, en plus spécifique
        // car ça rajoute le contentType dans les headers
        return $this->json(
            // les données à transformer en JSON
            $genreRepository->findAll(),
            // HTTP STATUS CODE
            200,
            // HTTP headers supplémentaires, dans notre cas : aucune
            [],
            // Contexte de serialisation, les groups de propriété que l'on veux serialise
            ['groups'=> 'list_genre']
        );
    }

    /**
     * @Route("/api/genres/{id}", name="api_genre")
     * Si le ParamConverter ne trouve pas de genre par l'ID, il nous donneras la valeur par défaut
     */
    public function showGenre(Genre $genre = null)
    {
        // dd($genre);
        // si c'est la valeur par défaut, alors le ParamConverter ne trouve pas de Genre avec cet id
        // on renvoit une message d'erreur
        if ($genre === null){
            // On a la possibilité de structurer notre réponse avec un objet à nous
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
     * @Route("/api/genres/{id}/movies", name="api_genre_movies")
     */
    public function showMoviesFromGenre(Genre $genre = null)
    {
        // dd($genre);
        if ($genre === null){
            $error = new JsonError(Response::HTTP_NOT_FOUND, 'Genre non trouvé');
            return $this->json($error, $error->getError());
        }

        // je ne renvoit que des films, j'ai donc besoin que de Groups sur Movie
        return $this->json(
            $genre->getMovies(),
            Response::HTTP_OK,
            [],
            ['groups' => ['list_movie']]
        );
    }
    
    /**
     * @Route("/api-factice/movies", name="api_movies_factice")
     */
    public function factice()
    {
        $test = new \App\Models\Movies();
        
        return $this->json($test->getAllMovies());
    }

    /**
     * @Route("/api/movies", name="api_movies_create", methods={"POST"})
     * @link https://symfony.com/doc/current/validation.html#using-the-validator-service
     */
    public function createMovie(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        /**
         * Pour test dans Insomnia
         * {
            "title": "titi",
            "type": "film",
            "summary": "lorem ispum dolor sit amet",
            "synopsis": "tititititiittit tititititiittit",
            "duration": 120,
            "rating": 5,
            "releaseDate": "1984-10-05T02:00:44+01:00",
            "poster": "https://m.media-amazon.com/images/M/MV5BYjg4ZjUzMzMtYzlmYi00YTcwLTlkOWUtYWFmY2RhNjliODQzXkEyXkFqcGdeQXVyNTUyMzE4Mzg@._V1_SX300.jpg"
            }
         */

        // de quoi j'ai besoin ?
        // TODO EntityManagerInterface pour faire persist, flush
        // TODO Request -> donnée que l'on recoit : le contenu de la requete
        $data = $request->getContent();
        // dd($data);
        /*
            {
                "title": "titi",
                "type": "film",
                "summary": "lorem ispum dolor sit amet",
                "synopsis": "tititititiittit tititititiittit",
                "duration": 120,
                "rating": 5,
                "releaseDate": "1984-10-05T02:00:44+01:00",
                "poster": "https://m.media-amazon.com/images/M/MV5BYjg4ZjUzMzMtYzlmYi00YTcwLTlkOWUtYWFmY2RhNjliODQzXkEyXkFqcGdeQXVyNTUyMzE4Mzg@._V1_SX300.jpg"
            }
        */
        // le hic c'est que le contenu est une chaine de caractères
        // il faut déserializer pour le tranformer en Objet
        //! Si il y a une liaison (Genre), notre deserialize ne fonctionnera pas
        //! car Doctrine n'est pas tenu au courant des objets à derserializer
        // pour cela on ajoute un denormalizer qui, à la manière d'un Voter
        // sera appeller si on a besoin de lui
        try { // je tente de faire le code dans les accolades
            
            $newMovie =  $serializer->deserialize($data, Movie::class, 'json');

        } catch (Exception $e){ // si une erreur est LANCE, je l'attrape
            // dd($e); // pour savoir quel type d'exception
            // si je veux être très précis sur le type d'exception
            // je peut mettre NotNormalizableValueException
            // je gère l'erreur, je renvoit un message
            return new JsonResponse("Hoouuu !! Ce qui vient d'arriver est de votre faute : JSON invalide", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        

        //dd($newMovie);

        //@link : https://symfony.com/doc/current/validation.html#using-the-validator-service
        $errors = $validator->validate($newMovie);
        // dd($errors);
        /*
            Symfony\Component\Validator\ConstraintViolationList {#1005 ▼
            -violations: []
            }
        */
        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $errorsString = (string) $errors;
    
            return new JsonResponse($errorsString, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        //dd($newMovie);

        /*
        App\Entity\Movie {#1029 ▼
        -id: null
        -title: "titi"
        -releaseDate: DateTime @465786044 {#995 ▶}
        -duration: 120
        -type: "film"
        -synopsis: "tititititiittit tititititiittit"
        -summary: "lorem ispum dolor sit amet"
        -rating: 5.0
        -poster: "https://m.media-amazon.com/images/M/MV5BYjg4ZjUzMzMtYzlmYi00YTcwLTlkOWUtYWFmY2RhNjliODQzXkEyXkFqcGdeQXVyNTUyMzE4Mzg@._V1_SX300.jpg"
        -seasons: Doctrine\Common\Collections\ArrayCollection {#1028 ▶}
        -reviews: Doctrine\Common\Collections\ArrayCollection {#1027 ▶}
        -genres: Doctrine\Common\Collections\ArrayCollection {#1026 ▶}
        -castings: Doctrine\Common\Collections\ArrayCollection {#1025 ▶}
        -slug: null
        -updatedAt: null
        }*/
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
     * @Route("/api/secure/genres", name="api_genres_create", methods={"POST"})
     * @link https://symfony.com/doc/current/validation.html#using-the-validator-service
     */
    public function createGenre(EntityManagerInterface $doctrine, Request $request, SerializerInterface $serializer, ValidatorInterface $validator): Response
    {
        $data = $request->getContent();
        try { 
            $newgenre =  $serializer->deserialize($data, Genre::class, 'json');
        } catch (Exception $e){ 

            return new JsonResponse("Hoouuu !! Ce qui vient d'arriver est de votre faute : JSON invalide", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        //@link : https://symfony.com/doc/current/validation.html#using-the-validator-service
        $errors = $validator->validate($newgenre);
        if (count($errors) > 0) {
            //dd($errors);
            $myJsonError = new JsonError(Response::HTTP_UNPROCESSABLE_ENTITY, "Des erreurs de validation ont été trouvées");
            $myJsonError->setValidationErrors($errors);


            //$errorsString = (string) $errors;
    
            return $this->json($myJsonError, $myJsonError->getError());
        }
        
        $doctrine->persist($newgenre);
        $doctrine->flush();

        return $this->json(
            $newgenre,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['show_genre']]
        );
    }
}
