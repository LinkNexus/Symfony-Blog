<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly Security $security)
    {
        parent::__construct($registry, Post::class);
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

    public function findAllAccessiblePosts(): ArrayCollection
    {
        $user = $this->security->getUser();

        $posts = $this->findAll();
        shuffle($posts);

        /**
         * @var ArrayCollection<Post> $collection
         */
        $collection = new ArrayCollection($posts);
        foreach ($posts as $post) {
            if (!in_array("ROLE_ADMIN", $user->getRoles())) {
                if (($post->getAudienceType() === 'friends_except' && $post->getPostAudience()->getUsers()->contains($user))) {
                    $collection->removeElement($post);
                }

                if (($post->getAudienceType() === 'specific_friends' &&
                    (!$post->getPostAudience()->getUsers()->contains($user) && $user !== $post->getOwner()))) {
                    $collection->removeElement($post);
                }

                if (($post->getAudienceType() === 'only_me' && $post->getOwner() !== $user)) {
                    $collection->removeElement($post);
                }


                foreach ($post->getOwner()->getBlocks() as $block) {
                    if ($block->getBlockedUser() === $user) {
                        $collection->removeElement($post);
                    }
                }

                foreach ($user->getSnoozes() as $snoozedUser) {
                    if ($snoozedUser->getSnoozedUser() === $post->getOwner()) {
                        $collection->removeElement($post);
                    }
                }
            }
        }

        return $collection;
    }
}
