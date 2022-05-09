<?php

namespace App\EventSubscriber;

use App\Repository\MovieRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class RandomMovieSubscriber implements EventSubscriberInterface
{
    /**
     * Repository Movie
     *
     * @var MovieRepository
     */
    private $movieRepository;

    /**
     * Classe pour manipuler Twig
     *
     * @var Twig\Environment
     */
    private $twig;

    public function __construct(MovieRepository $movieRepository, Environment $twig)
    {
        $this->movieRepository = $movieRepository;
        $this->twig = $twig;
    }

    /**
     * sélectionne un movie aléatoire et le donne à Twig
     *
     * @param ControllerEvent $event toutes les informations collecté jusqu'a présent
     * on a : 
     * * la request
     * * le nom du controller et sa méthode
     */
    public function onKernelController(ControllerEvent $event)
    {
        // pour débug
        // dump("Kernel.controller => random Movie");
        
        // dd($event);
        /*
        Symfony\Component\HttpKernel\Event\ControllerEvent {#234 ▼
            -controller: array:2 [▼
                0 => App\Controller\Front\MovieController {#207 ▶}
                1 => "showAll"
            ]
        */
        // TODO ne s'éxécuter que si le nom du controller contient 'App\Controller\Front'
        // je récupère l'objet Controller
        $controller = $event->getController();

        //! si c'est un tableau, je récupère que le premier
        if (is_array($controller)){$controller = $controller[0];}


        // je demande le nom de la classe de cet objet
        // eg : App\Controller\Front\MovieController
        $nomController = get_class($controller);
        
        if (strpos($nomController, 'App\Controller\Front') === false){
            // je n'ai pas trouvé 'App\Controller\Front\MovieController'
            // dans le nom du controller
            // je ne fait donc rien et je return
            return;
        }

        
        // TODO requete custom pour un film aléatoire
        $randomMovie = $this->movieRepository->findRandomMovie();

        // @link https://twig.symfony.com/doc/3.x/advanced.html#globals
        $this->twig->addGlobal('randomMovie', $randomMovie);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
