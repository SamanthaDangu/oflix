<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * @return Movie[]
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('movie')
            ->andWhere('movie.type = :type')
            ->setParameter('type', $type)
            ->orderBy('movie.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Les mieux notes en premier (NULL en dernier), limite au nombre demande.
     *
     * @return Movie[]
     */
    public function findTopRated(int $limit): array
    {
        return $this->createQueryBuilder('movie')
            ->orderBy('movie.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Movie[]
     */
    public function searchByTitleOrDescription(string $search): array
    {
        return $this->createQueryBuilder('movie')
            ->andWhere('LOWER(movie.title) LIKE :search OR LOWER(movie.summary) LIKE :search OR LOWER(movie.synopsis) LIKE :search')
            ->setParameter('search', '%' . mb_strtolower($search) . '%')
            ->orderBy('movie.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les donnees minimales d'un film aleatoire pour le bandeau global.
     *
     * On tire un offset aleatoire plutot que de faire `ORDER BY RAND()`,
     * qui trie la table entiere a chaque appel (appele sur chaque page front).
     */
    public function findRandomMovie(): ?array
    {
        $conn = $this->getEntityManager()->getConnection();

        $count = (int) $conn->executeQuery('SELECT COUNT(*) FROM movie')->fetchOne();
        if ($count === 0) {
            return null;
        }

        $results = $conn->executeQuery(
            'SELECT title, slug FROM movie LIMIT 1 OFFSET ?',
            [random_int(0, $count - 1)],
            [ParameterType::INTEGER]
        );

        return $results->fetchAssociative() ?: null;
    }
}
