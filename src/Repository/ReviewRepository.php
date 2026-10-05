<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Critiques postées par un utilisateur, triées des plus récentes aux plus anciennes,
     * avec le film associé pré-chargé pour éviter le N+1 dans le template.
     *
     * @return Review[]
     */
    public function findByUserOrderedByDate(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->addSelect('m')
            ->join('r.movie', 'm')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.watchedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
