<?php

namespace App\EventSubscriber;

use App\Repository\MovieRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class RandomMovieSubscriber implements EventSubscriberInterface
{
    private MovieRepository $movieRepository;

    private Environment $twig;

    public function __construct(MovieRepository $movieRepository, Environment $twig)
    {
        $this->movieRepository = $movieRepository;
        $this->twig = $twig;
    }

    /**
     * Ajoute un film aleatoire aux variables Twig des pages front.
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $nomController = get_class($controller);

        if (strpos($nomController, 'App\Controller\Front') === false) {
            return;
        }

        $randomMovie = $this->movieRepository->findRandomMovie();

        $this->twig->addGlobal('randomMovie', $randomMovie);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
