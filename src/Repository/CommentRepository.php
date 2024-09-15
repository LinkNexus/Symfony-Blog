<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    //    /**
    //     * @return Comment[] Returns an array of Comment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByReactionsNumber(Post $post, string $criteria, EntityManagerInterface $entityManager): array
    {
        /* return $this->createQueryBuilder("c")
            ->leftJoin("c.commentReactions", "r")
            ->orderBy("COUNT(r.id)", "ASC")
            ->getQuery()
            ->getArrayResult(); */

       $comments = $entityManager->getRepository(Comment::class)
           ->findBy([
               "post" => $post,
               "respondedComment" => null
           ]);

       if ($criteria === "ASC") {
           usort($comments, function ($a, $b) {
               if ($a->getCommentReactions()->count() === $b->getCommentReactions()->count())
                   return 0;
               return $a->getCommentReactions()->count() > $b->getCommentReactions()->count() ? 1 : -1;
           });
       } else {
           usort($comments, function ($a, $b) {
               if ($a->getCommentReactions()->count() === $b->getCommentReactions()->count())
                   return 0;
               return $a->getCommentReactions()->count() > $b->getCommentReactions()->count() ? -1 : 1;
           });
       }

       return $comments;
    }
}
