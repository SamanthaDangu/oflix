<?php

namespace App\Controller\Back;

use App\Entity\Movie;
use App\Form\MovieType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/movie")
 */
class MovieController extends AbstractController
{
    /**
     * @Route("/", name="back_movie_index", methods={"GET"})
     */
    public function index(MovieRepository $movieRepository): Response
    {
        return $this->render('back/movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="back_movie_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // pour vérifier les autorisations on peut utiliser denyAccessUnlessGranted 
            // si l'utilisateur n'est pas connecté => redirection vers la page de login
            // si l'utilisateur n'a pas le role demandé (y compris avec le role hierarchy) => 403 non authorisé
            // sinon symfony ne fait rien et le code continue
        $this->denyAccessUnlessGranted('AJOUT_DU_MOVIE');
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // TODO calcul du slug => fait dans le Listener

            $entityManager->persist($movie);
            
            $entityManager->flush();

            return $this->redirectToRoute('back_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/movie/new.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="back_movie_show", methods={"GET"})
     */
    public function show(Movie $movie): Response
    {
        return $this->render('back/movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="back_movie_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Movie $movie, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted("MODIFICATION_DU_MOVIE", $movie);
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // TODO calcul du slug => fait dans le Listener

            // on rajoute une notification dans le tableau des notifs
            $this->addFlash(
                // clé du tableau
                'notice-jb',
                // le message
                'Les modifications ont été bien sauvegardées!'
            );

            $entityManager->flush();

            return $this->redirectToRoute('back_movie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/movie/edit.html.twig', [
            'movie' => $movie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="back_movie_delete", methods={"POST"})
     */
    public function delete(Request $request, Movie $movie, EntityManagerInterface $entityManager): Response
    {

        if ($this->isCsrfTokenValid('delete'.$movie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($movie);
            $entityManager->flush();
        } else {
            // on rajoute une notification dans le tableau des notifs
            $this->addFlash(
                // clé du tableau
                'error',
                // le message
                'Petit chenapan, ton token n\'est pas valide!'
            );
        }

        return $this->redirectToRoute('back_movie_index', [], Response::HTTP_SEE_OTHER);
    }
}
