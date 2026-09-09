<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Classe d'accès à l'API de themoviedb.org (TMDb)
 */
class TmdbApi
{
    private const BASE_URL = 'https://api.themoviedb.org/3';
    private const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p/w500';

    private $httpClient;
    private $parameterBag;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
    }

    /**
     * Renvoie une page de la liste "popular" ou "top_rated" pour un type de media donne.
     *
     * @param string $mediaType 'movie' ou 'tv'
     * @param string $listName  'popular' ou 'top_rated'
     *
     * @return array|null Le tableau de resultats ('results'), ou null en cas d'echec
     */
    public function fetchList(string $mediaType, string $listName, int $page): ?array
    {
        $content = $this->request("/{$mediaType}/{$listName}", ['page' => $page]);

        return $content['results'] ?? null;
    }

    /**
     * Renvoie une page de resultats filtres par plateforme de streaming (region France).
     *
     * @param string $mediaType   'movie' ou 'tv'
     * @param string $providerIds Identifiants TMDb des plateformes, separes par "|" (OR)
     *
     * @return array|null Le tableau de resultats ('results'), ou null en cas d'echec
     */
    public function fetchDiscover(string $mediaType, string $providerIds, int $page): ?array
    {
        $content = $this->request("/discover/{$mediaType}", [
            'with_watch_providers' => $providerIds,
            'watch_region' => 'FR',
            'sort_by' => 'popularity.desc',
            'page' => $page,
        ]);

        return $content['results'] ?? null;
    }

    /**
     * Renvoie le detail complet d'un film/serie, casting inclus (et saisons pour les series).
     *
     * @param string $mediaType 'movie' ou 'tv'
     */
    public function fetchDetails(string $mediaType, int $id): ?array
    {
        return $this->request("/{$mediaType}/{$id}", ['append_to_response' => 'credits']);
    }

    /**
     * Construit l'URL complete d'une affiche a partir du poster_path renvoye par TMDb.
     */
    public function buildPosterUrl(?string $posterPath): ?string
    {
        if (!$posterPath) {
            return null;
        }

        return self::IMAGE_BASE_URL . $posterPath;
    }

    private function request(string $path, array $query): ?array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                self::BASE_URL . $path,
                [
                    'query' => array_merge($query, [
                        'api_key' => $this->parameterBag->get('app.tmdb_api_key'),
                        'language' => 'fr-FR',
                    ]),
                ]
            );

            return $response->toArray();
        } catch (ExceptionInterface $e) {
            $statusCode = $e instanceof HttpExceptionInterface ? $e->getResponse()->getStatusCode() : null;
            $this->logger->error('Appel TMDb échoué sur {path}{status} : {message}', [
                'path' => $path,
                'status' => $statusCode !== null ? " (HTTP {$statusCode})" : '',
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
