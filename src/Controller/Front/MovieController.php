<?php

namespace App\Controller\Front;

use App\Controller\CsrfProtectedControllerTrait;
use App\Entity\Movie;
use App\Entity\User;
use App\Repository\CastingRepository;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MovieController extends AbstractController
{
    use CsrfProtectedControllerTrait;

    /**
     * Affiche la fiche d'un film ou d'une serie avec les critiques associees.
     *
     * @param Movie $movie Film résolu depuis le slug
     */
    #[Route('/movie/{slug}', name: 'movie', methods: ['GET'])]
    public function show(Movie $movie, ReviewRepository $reviewRepository, CastingRepository $castingRepository): Response
    {
        $reviews = $reviewRepository->findBy(['movie' => $movie], ['id' => 'DESC']);

        $user = $this->getUser();
        $userReview = $user instanceof User
            ? $reviewRepository->findOneBy(['movie' => $movie, 'user' => $user])
            : null;

        return $this->render('front/movie/show.html.twig', [
            'movie' => $movie,
            'reviews' => $reviews,
            'userReview' => $userReview,
            'castings' => $castingRepository->findByMovieWithActor($movie),
        ]);
    }

    /**
     * Affiche la home ou le catalogue, avec recherche quand le parametre search est fourni.
     */
    #[Route('/', name: 'movie_home', methods: ['GET'])]
    #[Route('/catalogue', name: 'catalogue', methods: ['GET'])]
    public function showAll(MovieRepository $repository, Request $request): Response
    {
        $isHome = $request->attributes->get('_route') === 'movie_home';

        if ($isHome) {
            $featuredMovies = $repository->findTopRated(8);

            return $this->render('front/movie/list.html.twig', [
                'movies' => [],
                'heroMovie' => $featuredMovies[0] ?? null,
                'featuredMovies' => $featuredMovies,
                'films' => $repository->findByType('Movie'),
                'series' => $repository->findByType('Series'),
                'isHome' => true,
                'pageTitle' => null,
                'searchQuery' => '',
            ]);
        }

        $searchQuery = trim((string) $request->query->get('search', ''));
        $movies = $searchQuery !== ''
            ? $repository->searchByTitleOrDescription($searchQuery)
            : $repository->findAll();

        return $this->render('front/movie/list.html.twig', [
            'movies' => $movies,
            'heroMovie' => null,
            'featuredMovies' => [],
            'films' => [],
            'series' => [],
            'isHome' => false,
            'pageTitle' => $searchQuery !== '' ? 'Recherche' : 'Catalogue',
            'searchQuery' => $searchQuery,
        ]);
    }

    /**
     * Affiche le catalogue filtre par type de programme.
     */
    #[Route('/films', name: 'films', methods: ['GET'])]
    #[Route('/series', name: 'series', methods: ['GET'])]
    public function showByType(MovieRepository $repository, Request $request): Response
    {
        $isFilmsPage = $request->attributes->get('_route') === 'films';
        $type = $isFilmsPage ? 'Movie' : 'Series';

        return $this->render('front/movie/list.html.twig', [
            'movies' => $repository->findByType($type),
            'pageTitle' => $isFilmsPage ? 'Films' : 'Séries',
            'heroMovie' => null,
            'featuredMovies' => [],
            'films' => [],
            'series' => [],
            'isHome' => false,
            'searchQuery' => '',
        ]);
    }

    #[Route('/movie/{slug}/favorite', name: 'movie_favorite_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addFavorite(Movie $movie, Request $request, EntityManagerInterface $entityManager, #[CurrentUser] User $user): Response
    {
        $this->assertCsrfTokenValid('favorite_' . $movie->getId(), $request);

        $user->addFavoriteMovie($movie);
        $entityManager->flush();
        $this->addFlash('success', sprintf('%s a été ajouté à votre liste.', $movie->getTitle()));

        return $this->redirectToRoute('movie', ['slug' => $movie->getSlug()]);
    }

    #[Route('/movie/{slug}/favorite/remove', name: 'movie_favorite_remove', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function removeFavorite(Movie $movie, Request $request, EntityManagerInterface $entityManager, #[CurrentUser] User $user): Response
    {
        $this->assertCsrfTokenValid('favorite_' . $movie->getId(), $request);

        $user->removeFavoriteMovie($movie);
        $entityManager->flush();
        $this->addFlash('success', sprintf('%s a été retiré de votre liste.', $movie->getTitle()));

        return $this->redirectToRoute('movie', ['slug' => $movie->getSlug()]);
    }
}
