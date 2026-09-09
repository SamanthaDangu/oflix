<?php

namespace App\Repository;

use App\Entity\Casting;
use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Casting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Casting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Casting[]    findAll()
 * @method Casting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CastingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Casting::class);
    }

    /**
     * Castings d'un film avec l'acteur charge en une seule requete (evite le N+1 sur casting.actor).
     *
     * @return Casting[]
     */
    public function findByMovieWithActor(Movie $movie): array
    {
        return $this->createQueryBuilder('casting')
            ->addSelect('actor')
            ->join('casting.actor', 'actor')
            ->andWhere('casting.movie = :movie')
            ->setParameter('movie', $movie)
            ->orderBy('casting.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
