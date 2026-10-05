<?php

namespace App\Controller\Front;

use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeoController extends AbstractController
{
    #[Route('/robots.txt', name: 'robots', methods: ['GET'])]
    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /back/',
            'Disallow: /profile',
            'Disallow: /favorites',
            'Disallow: /api/secure',
            '',
            'Sitemap: ' . $this->generateUrl('sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    /**
     * Sitemap genere a la volee : pages statiques + fiches films + genres.
     */
    #[Route('/sitemap.xml', name: 'sitemap', methods: ['GET'])]
    public function sitemap(MovieRepository $movieRepository, GenreRepository $genreRepository): Response
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        foreach (['movie_home', 'catalogue', 'films', 'series'] as $routeName) {
            $xml->addChild('url')->addChild('loc', htmlspecialchars($this->generateUrl($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL)));
        }

        foreach ($movieRepository->findAll() as $movie) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($this->generateUrl('movie', ['slug' => $movie->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)));
            if ($movie->getUpdatedAt()) {
                $url->addChild('lastmod', $movie->getUpdatedAt()->format('Y-m-d'));
            }
        }

        foreach ($genreRepository->findAll() as $genre) {
            $xml->addChild('url')->addChild('loc', htmlspecialchars($this->generateUrl('genre', ['slug' => $genre->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)));
        }

        return new Response($xml->asXML(), Response::HTTP_OK, ['Content-Type' => 'application/xml']);
    }
}
