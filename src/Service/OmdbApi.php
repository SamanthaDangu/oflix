<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Classe d'accès à l'API d'omdbapi.com
 */
class OmdbApi
{
    // Les services nécessaires
    // On utilise le composant HttpClient de Symfony
    // @link https://symfony.com/doc/current/http_client.html
    private $httpClient;
    // Pour récupérer les paramètres de services.yaml (mais pas que !) depuis notre code
    // /!\ On pourrait tranmsettre la clé API directemnt via le constructeur comme fait précédemment
    // Autre façon de faire et qui permet d'accéder à tous les paramètres du conteneur de services
    // https://symfony.com/blog/new-in-symfony-4-1-getting-container-parameters-as-a-service
    private $parameterBag;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $parameterBag)
    {
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
    }

    /**
     * Renvoie le contenu JSON du film demandé
     * 
     * @param string $title Movie title
     */
    public function fetch(string $title)
    {
        // On envoie une requête chez omdbapi.com
        $response = $this->httpClient->request(
            'GET',
            'https://www.omdbapi.com/',
            // @link https://symfony.com/doc/current/http_client.html#query-string-parameters
            [
                'query' => [
                    't' => $title, // urlencode() sera appliqué dessus
                    // /!\ On pourrait tranmsettre la clé API directemnt via le constructeur comme fait précédemment
                    // Autre façon de faire et qui permet d'accéder à tous les paramètres du conteneur de services
                    'apiKey' => $this->parameterBag->get('app.omdb_api_key'),
                ]
            ]
        );

        // On convertit le JSON en tableau PHP
        $responseArray = $response->toArray();

        return $responseArray;
    }

    /**
     * Renvoie l'URL du poster d'un film donné
     * 
     * @param string $title Movie title
     * 
     * @return string Poster's URL
     */
    public function fetchPoster(string $title)
    {
        $content = $this->fetch($title);

        // Le poster est-il manquant ?
        if (!array_key_exists('Poster', $content)) {
            return false;
        }

        return $content['Poster'];
    }
}