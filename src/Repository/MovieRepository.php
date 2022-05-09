<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @link https://symfony.com/doc/current/doctrine.html#querying-with-the-query-builder
     * 
     * @return Movie[] Returns an array of Movie objects
    */
    public function findAllOrderedByTitle()
    {
        // pour faire une requete je doit donner un nom à la table: un alias
        // comme dans le FROM SQL : FROM table t_alias
        $resultats = $this->createQueryBuilder('m')
            // à partir d'ici j'utilise l'alias pour représenter ma table
            // je tri sur le title de la table
            ->orderBy('m.title', 'DESC')

            // l'avant dernière instruction est de générer la requete
            ->getQuery()
            // et la dernière instruction est d'éxecuter la requete
            // on reçoit donc les résultats à partir de là
            ->getResult();

        return $resultats;
    }

    /**
     * @link https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
     * 
     * @return Movie[] Returns an array of Movie objects
    */
    public function findAllOrderedByTitleDQL()
    {
        // le repository ne sais pas faire de DQL
        // on est obligé d'apeller le Manager
        $entityManager = $this->getEntityManager();

        // on utilise le système d'alias pour représenter notre Entity
        // Dnas le select on dit que l'on veut TOUTE l'entité en utilisant l'alias
        $query = $entityManager->createQuery(
            'SELECT m
            FROM App\Entity\Movie m
            ORDER BY m.title DESC');
        
        /*
        $query = $entityManager->createQuery(
            'SELECT tagada.title
            FROM App\Entity\Movie tagada
            ORDER BY tagada.releaseDate DESC');
        */

        // returns an array of Product objects
        $resultats = $query->getResult();
        // dd($resultats);
        return $resultats;
    }

    public function findRandomMovie()
    {
        // ni le repository, ni le manager savent faire du SQL
        // on descend donc d'un cran
        // pour aller chercher l'équivalent de PDO
        $conn = $this->getEntityManager()->getConnection();

        // une requete qui renvoit un title / slug aléatoire
        $sql = '
            SELECT title, slug FROM movie
            ORDER BY RAND()
            LIMIT 1
            ';

        // exécution de la requete
        $results = $conn->executeQuery($sql);

        // returns an array (i.e. a raw data set)
        return $results->fetchAssociative();
    }
    
    // /**
    //  * @return Movie[] Returns an array of Movie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Movie
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
