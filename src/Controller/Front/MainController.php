<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="main_")
 */
class MainController extends AbstractController
{

    /*
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    */

    /**
     * User favorites list
     * 
     * @Route("/favorites", name="favorites")
     */
    public function favorites()
    {
        return $this->render('front/main/favorites.html.twig');
    }

    /**
     * changement de theme
     * 
     * @Route("/theme/toggle", name="theme_switcher")
     *
     */
    public function themeSwitcher(SessionInterface $session): response
    {
        // TODO déplacer dans le UserController

        // j'ai besoin d'une classe gérée par le FW
        // Cette classe je veux que le FW me l'instancie/crée en auto
        // Pour cela j'utilise le principe d'injection de dépendance

        // Mon code est dépendant d'une classe : SessionInterface
        
        //* Objectif pouvoir changer de theme
        // stocker le nom du theme actif, et/ou passer à l'autre theme

        // @link https://symfony.com/doc/5.4/components/http_foundation/sessions.html#attributes
        // si il n'y a pas de clé 'theme' (c'est la première fois que je vois cette utilisateur)
        if (!$session->has('theme')){
            // je met la valeur par défaut : netflix
            $session->set('theme', 'netflix');
        }

        // anciennement $_SESSION['theme']
        // fournit la valeur stocké pour la clé (theme)
        $theme = $session->get('theme');

        // il existe une version raccourci pour à la afois tester si la clé existe 
        // et nous DONNER une valeur par défaut si la clé n'existe pas
        //! cela ne SET pas la valeur par défaut
        // $theme = $session->get('theme', 'netflix');

        // je change de theme suivant le theme actif
        if ($theme === 'netflix'){
            $session->set('theme', 'allocine');
        } else {
            $session->set('theme', 'netflix');
        }
        
        // Qu'est ce que je veux afficher ?
        // --> Quelle page HTML donc quel Twig ??
        // --> Aucun en particulier, la page actuelle
        // On a pas de solution pour la page actuelle, donc on va utiliser un subterfuge
        // et redireger l'utilisateur sur la page home

        // TODO UX redirect vers la page courante
        return $this->redirectToRoute("movie_home");
    }
}