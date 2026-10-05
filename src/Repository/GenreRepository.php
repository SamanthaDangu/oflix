<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Genre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Genre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Genre[]    findAll()
 * @method Genre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    /**
     * Genres tries par nom, avec leurs movies pre-chargees (evite le N+1
     * quand le template affiche le nombre de films par genre).
     *
     * @return Genre[]
     */
    public function findAllWithMovies(): array
    {
        return $this->createQueryBuilder('genre')
            ->addSelect('movie')
            ->leftJoin('genre.movies', 'movie')
            ->orderBy('genre.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
