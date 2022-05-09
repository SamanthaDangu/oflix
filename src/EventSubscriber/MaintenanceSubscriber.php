<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    /**
     * boolean venant du fichier service.yaml, qui va lire le fichier .env
     *
     * @var bool
     */
    private $isMaintenanceEnabled;

    public function __construct(bool $maintenanceEnabled, $test)
    {
        // pour débugger le passage de variable string <-> bool
        // dump($test);
        $this->isMaintenanceEnabled = $maintenanceEnabled;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        // Si le paramètre de maintenance est désactivé je ne fait rien
        if (!$this->isMaintenanceEnabled) { return; }
        

        // Objectif : modifier le contenu HTML en y ajoutant notre message de maintenance
        // TODO Récuperer le contenu HTML de la réponse
        $htmlContent = $event->getResponse()->getContent();
        // on peut voir le contenu, mais il ne sera pas indenté / lisible
        // dd($htmlContent);

        // TODO exclure le profiler
        //  regex qui cible le profiler et wdt : ^/(_(profiler|wdt))/
        $request = $event->getRequest();
        //dd($request);
        /** Symfony\Component\HttpFoundation\Request {#3 ▼
          #pathInfo: "/_profiler"
        */
        $route = $request->getPathInfo();
        // Si la route contient le profiler/wdt on s'arrête ici
        if (preg_match('/^\/(_(profiler|wdt))/', $route))
        {
            return;
        }

        // TODO remplacer une partie du contenu avec mon message => injecter du contenu
        $newHtmlContent = str_replace(
            '<div id="broadcast_message"></div>', 
            '<div id="broadcast_message" class="alert alert-danger">Maintenance prévue mardi 15 février à 17h00</div>',
            $htmlContent);
        // Pour que cela apparaise partout même dans le profiler
        $newHtmlContent = str_replace(
            '<body>', 
            '<body><div id="broadcast_message" class="alert alert-danger">Maintenance prévue mardi 15 février à 17h00</div>',
            $htmlContent);
        // TODO donner le contenu modifié à la réponse 
        $response = $event->getResponse();
        $response->setContent($newHtmlContent);

        // pas besoin de retourner la réponse car le FW s'en occupe
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'onKernelResponse',
        ];
    }
}
