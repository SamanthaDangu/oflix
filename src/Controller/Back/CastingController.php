<?php

namespace App\Controller\Back;

use App\Entity\Casting;
use App\Entity\Movie;
use App\Form\CastingType;
use App\Repository\CastingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/back/casting")
 */
class CastingController extends AbstractController
{
    /**
     * @Route("/", name="back_casting_index", methods={"GET"})
     */
    public function index(CastingRepository $castingRepository): Response
    {
        return $this->render('back/casting/index.html.twig', [
            'castings' => $castingRepository->findAll(),
        ]);
    }
    
    /**
     * @Route("/movie/{id}", name="back_casting_index_by_movie", methods={"GET"})
     */
    public function listByMovie(CastingRepository $castingRepository, Movie $movie): Response
    {

        //$castings = $castingRepository->findAll();
        $castings = $castingRepository->findBy(['movie'=>$movie]);


        return $this->render('back/casting/index.html.twig', [
            'castings' => $castings,
        ]);
    }


    /**
     * @Route("/new", name="back_casting_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $casting = new Casting();
        $form = $this->createForm(CastingType::class, $casting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // TODO Est ce que le créditOrder existe pas déjà ?

            $entityManager->persist($casting);
            $entityManager->flush();

            return $this->redirectToRoute('back_casting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/casting/new.html.twig', [
            'casting' => $casting,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="back_casting_show", methods={"GET"})
     */
    public function show(Casting $casting): Response
    {
        return $this->render('back/casting/show.html.twig', [
            'casting' => $casting,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="back_casting_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Casting $casting, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CastingType::class, $casting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_casting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/casting/edit.html.twig', [
            'casting' => $casting,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="back_casting_delete", methods={"POST"})
     */
    public function delete(Request $request, Casting $casting, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$casting->getId(), $request->request->get('_token'))) {
            $entityManager->remove($casting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_casting_index', [], Response::HTTP_SEE_OTHER);
    }
}
