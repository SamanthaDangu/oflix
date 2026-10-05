<?php

namespace App\Controller\Front;

use App\Controller\CsrfProtectedControllerTrait;
use App\Entity\Movie;
use App\Entity\Review;
use App\Entity\User;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewController extends AbstractController
{
    use CsrfProtectedControllerTrait;

    /**
     * Ajout ou modification de la critique de l'utilisateur connecté pour ce film
     *
     * @link https://symfony.com/doc/current/best_practices.html#use-a-single-action-to-render-and-process-the-form
     */
    #[Route('/movie/{slug}/review', name: 'movie_review_add', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function show(Movie $movie, Request $request, EntityManagerInterface $doctrine, ReviewRepository $reviewRepository, #[CurrentUser] User $user)
    {
        // un utilisateur ne peut avoir qu'une seule critique par film : on la réutilise si elle existe
        $review = $reviewRepository->findOneBy(['movie' => $movie, 'user' => $user]) ?? new Review();
        $isEditing = $review->getId() !== null;

        $review->setUser($user);
        $review->setUsername($user->getDisplayName());
        $review->setEmail($user->getEmail());
        if (!$isEditing) {
            $review->setWatchedAt(new DateTimeImmutable());
        }
        $form = $this->createForm(ReviewType::class, $review);

        // on dit au formulaire de prendre en compte la requete HTTP
        // et donc de relier les données envoyé par le formulaire
        // à la variable que nous lui avons fournit à la création du formulaire
        // $review
        $form->handleRequest($request);

        // si le formulaire est renvoyé ET qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // comme la on a commenté movie dans notre formulaire
            // il faut maintenant faire la liaison
            $review->setMovie($movie);

            $doctrine->persist($review);
            $doctrine->flush();

            $this->addFlash('success', $isEditing ? 'Votre critique a été mise à jour.' : 'Votre critique a été publiée.');

            return $this->redirectToRoute('movie', ['slug' => $movie->getSlug()]);
        }

        return $this->render('front/review/index.html.twig', [
            'movie' => $movie,
            'form' => $form->createView(),
            'isEditing' => $isEditing,
            'review' => $review,
        ]);
    }

    /**
     * Suppression de la critique de l'utilisateur connecté pour ce film
     */
    #[Route('/movie/{slug}/review/delete', name: 'movie_review_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Movie $movie, Request $request, EntityManagerInterface $doctrine, ReviewRepository $reviewRepository, #[CurrentUser] User $user): Response
    {
        $review = $reviewRepository->findOneBy(['movie' => $movie, 'user' => $user]);

        if (!$review) {
            throw $this->createNotFoundException();
        }

        $this->assertCsrfTokenValid('delete_review_' . $review->getId(), $request);

        $doctrine->remove($review);
        $doctrine->flush();

        $this->addFlash('success', 'Votre critique a été supprimée.');

        return $this->redirectToRoute('movie', ['slug' => $movie->getSlug()]);
    }
}
