<?php

namespace App\Controller\Back;

use App\Controller\CsrfProtectedControllerTrait;
use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/movie')]
class MovieController extends AbstractController
{
    use CsrfProtectedControllerTrait;

    #[Route('/', name: 'back_movie_index', methods: ['GET'])]
    public function index(MovieRepository $movieRepository): Response
    {
        return $this->render('back/movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'back_movie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($movie);
            $entityManager->flush();

            return $this->redirectToRoute('back_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'back_movie_show', methods: ['GET'])]
    public function show(Movie $movie): Response
    {
        return $this->render('back/movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route('/{id}/edit', name: 'back_movie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Movie $movie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Les modifications ont été bien sauvegardées!');

            $entityManager->flush();

            return $this->redirectToRoute('back_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'back_movie_delete', methods: ['POST'])]
    public function delete(Request $request, Movie $movie, EntityManagerInterface $entityManager): Response
    {
        $this->assertCsrfTokenValid('delete' . $movie->getId(), $request);

        $entityManager->remove($movie);
        $entityManager->flush();

        return $this->redirectToRoute('back_movie_index', [], Response::HTTP_SEE_OTHER);
    }
}
