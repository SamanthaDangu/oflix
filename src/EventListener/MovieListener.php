<?php

namespace App\EventListener;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use App\Service\MySlugger;

class MovieListener
{
    private $slugger;

    private $movieRepository;

    public function __construct(MySlugger $slugger, MovieRepository $movieRepository)
    {
        $this->slugger = $slugger;
        $this->movieRepository = $movieRepository;
    }

    public function updateSlug(Movie $movie)
    {
        $baseSlug = $this->slugger->slugify($movie->getTitle());
        $movie->setSlug($this->makeUniqueSlug($baseSlug, $movie->getId()));
    }

    /**
     * Garantit l'unicite du slug (deux films peuvent avoir le meme titre,
     * ex: deux versions de "La vie est belle") en ajoutant un suffixe
     * numerique en cas de collision.
     */
    private function makeUniqueSlug(string $baseSlug, ?int $excludeId): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $qb = $this->movieRepository->createQueryBuilder('movie')
            ->select('COUNT(movie.id)')
            ->andWhere('movie.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('movie.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
