<?php

namespace App\EventListener;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use App\Service\MySlugger;

class GenreListener
{
    private $slugger;

    private $genreRepository;

    public function __construct(MySlugger $slugger, GenreRepository $genreRepository)
    {
        $this->slugger = $slugger;
        $this->genreRepository = $genreRepository;
    }

    public function updateSlug(Genre $genre)
    {
        $baseSlug = $this->slugger->slugify($genre->getName());
        $genre->setSlug($this->makeUniqueSlug($baseSlug, $genre->getId()));
    }

    /**
     * Garantit l'unicite du slug en cas de collision entre deux noms
     * differents qui se sluggifieraient de la meme facon.
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
        $qb = $this->genreRepository->createQueryBuilder('genre')
            ->select('COUNT(genre.id)')
            ->andWhere('genre.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('genre.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
