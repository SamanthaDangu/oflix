<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    /**
     * @Route("/support", name="support")
     */
    public function index(): Response
    {
        return $this->render('front/support/index.html.twig', [
            'controller_name' => 'SupportController',
        ]);
    }

    /**
     * Ajoute 1 au compteur
     * @Route("/support/add/{nb}")
     * @param SessionInterface $session
     */
    public function add(SessionInterface $session, int $nb)
    {
        // je teste l'existence de la clé 
        // si je ne lea trouve pas je vais y mettre une valeur par défaut
        if (!$session->has('compteur'))
        {
            $session->set('compteur', 1);
        }


        // lecture de la valeur
        $monCompteur = $session->get('compteur');
        
        // je veux stocker un compteur
        // j'écrit dans la session
        // $_SESSION['compteur'] = 1
        $session->set('compteur', $monCompteur+$nb);

        // lecture de la valeur
        $monCompteur = $session->get('compteur');

        return $this->render('front/support/index.html.twig', [
            'controller_name' => 'SupportController',
        ]);
    }



    function truc(SessionInterface $s)
    {
        $maVariable = "";

        if (!$s->has('cle'))
        {
            $maVariable = "valeur par default";
        } else {
            $maVariable = $s->get('cle');
        }
        
        // identique en une seulle ligne
        $maVariable = $s->get('cle', 'valeur par default');
    }
}
