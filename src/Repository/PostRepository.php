<?php

namespace App\Repository;

use App\Entity\Rubrik;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
    public function findByRubrik(Rubrik $rubrik): array
    {
    return $this->createQueryBuilder('p')
    ->andWhere('p.rubrik = :rubrik')
    ->setParameter('rubrik', $rubrik)
    ->getQuery()
    ->getResult();
    }
    public function findTwoPostsFromSameRubrik(int $rubrikId, int $currentPostId)
    {
    return $this->createQueryBuilder('p')
        ->where('p.rubrik = :rubrikId')
        ->andWhere('p.id != :currentPostId')
        ->setParameter('rubrikId', $rubrikId)
        ->setParameter('currentPostId', $currentPostId)
        ->orderBy('p.createdAt', 'DESC')
        ->setMaxResults(2)
        ->getQuery()
        ->getResult();
    }


    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
