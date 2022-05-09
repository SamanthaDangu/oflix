<?php

namespace App\Controller\Front;

use App\Entity\Movie;
use App\Entity\Review;
use App\Repository\CastingRepository;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\RepositoryException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    /**
     * * Display movie/serie
     * 
     * @param string $slug Slug of the movie to display
     * 
     * @Route("/movie/{slug}", name="movie")
     */
    public function show(Movie $movie, CastingRepository $castingRepo, ReviewRepository $reviewRepository): Response
    {
        //$movie = $movieRepos->find($id);
        // plus besoin de récupérer le Repository de Movie
        // /!\ On a récupéré $movie via le ParamConverter depuis l'URL

        // On voit avec le dump que la propriété seasons n'est pas remplit 
        //dump($movie);
        
        // je veux les casting d'un film en particulier : $id
        // j'utilise le findBy pour faire un find avec un critère
        // en SQL : movie_id = $id
        // Je suis en objet/entité
        // je dit donc : fait un filtre sur la propriété 'movie' de l'objet 'casting'
        // la valeur de cette propriété doit être égale à un objet Movie
        // je lui donne donc l'objet $movie pour faire le filtre
        // $criteria : ['propriété' => valeur]
        // $orderBy : ['propriété' => 'ASC/DESC']
        $castingsFilterByMovie = $castingRepo->findBy(['movie' => $movie], ['creditOrder' => 'ASC']);
        //dump($castingsFilterByMovie);


        // Je vais chercher la dernière review du film
        // ça me renvoit un tableau, même si j'ai demandé 1 résultat
        $lastReviews = $reviewRepository->findBy(['movie' => $movie], ['id' => 'DESC'], 1);
        //dump($lastReviews);
        
            return $this->render('front/movie/show.html.twig', [
                'movie' => $movie,
                'castingsFilterByMovie' => $castingsFilterByMovie,
                'lastReviews' => $lastReviews
            ]);
    }
    
    /**
     * show all movies
     * @Route("/", name="movie_home")
     * @Route("/movies", name="movies")
     * @param MovieRepository $repository
     * @return Response
     */
    public function showAll(MovieRepository $repository): Response
    {
          $movies = $repository->findAll();
          //$movies = $repository->findAllOrderedByTitle();
          //$movies = $repository->findAllOrderedByTitleDQL();
          //dump($movies);

          return $this->render('front/movie/list.html.twig', [
            'movies' => $movies
        ]);
    }

}
