<?php

namespace App\Controller\Front;

use App\Entity\Movie;
use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{

    /**
     * Methode pour l'ajout de review
     * 
     * @link https://symfony.com/doc/current/best_practices.html#use-a-single-action-to-render-and-process-the-form
     * 
     * @Route("/movie/{id}/review", name="movie_review_add")
     */
    public function show(Movie $movie, Request $request, EntityManagerInterface $doctrine)
    {

        // Création du formulaire pour ajouter un review
        $review = new Review();
        $formulaire = $this->createForm(ReviewType::class, $review);

        // on dit au formulaire de prendre en compte la requete HTTP
        // et donc de relier les données envoyé par le formulaire
        // à la variable que nous lui avons fournit à la création du formulaire
        // $review
        $formulaire->handleRequest($request);

        // si le formulaire est renvoyé ET qu'il est valide
        if ($formulaire->isSubmitted() && $formulaire->isValid()){

            // comme la on a commenté movie dans notre formulaire
            // il faut maintenant faire la liaison
            $review->setMovie($movie);

            // dd($review);
            $doctrine->persist($review);
            $doctrine->flush();

            return $this->redirectToRoute('movie', ['slug' => $movie->getSlug()]);
        }

        return $this->renderForm('front/review/index.html.twig',[
            'movie' => $movie,
            'formulaire' => $formulaire
        ]);
    }
}
