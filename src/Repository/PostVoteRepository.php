<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostVote;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostVote>
 */
class PostVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostVote::class);
    }

    /**
     * @return PostVote[]
     */
    public function findFor(User $user, Post $post): array
    {
        return $this->createQueryBuilder('d_pv')
            ->where('d_pv.user = :user')
            ->andWhere('d_pv.post = :post')
            ->setParameter('user', $user)
            ->setParameter('post', $post)
            ->getQuery()
            ->getResult();
    }
}
