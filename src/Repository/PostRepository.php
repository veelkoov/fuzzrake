<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Submission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @return Post[]
     */
    public function getSubmissionTopics(Submission $submission): array
    {
        // Optimization opportunity: return COUNT of votes instead of entities
        return $this->createQueryBuilder('d_p_topic')
            ->leftJoin('d_p_topic.responses', 'd_p_response')
            ->join('d_p_topic.user', 'd_u1')
            ->leftJoin('d_p_topic.votes', 'd_v1')
            ->leftJoin('d_p_response.user', 'd_u2')
            ->leftJoin('d_p_response.votes', 'd_v2')
            ->where('d_p_topic.submission = :submission')
            ->andWhere('d_p_topic.parent IS NULL')
            ->setParameter('submission', $submission)
            ->orderBy('d_p_topic.postedUtc')
            ->addOrderBy('d_p_topic.id')
            ->addOrderBy('d_p_response.postedUtc')
            ->addOrderBy('d_p_response.id')
            ->getQuery()
            ->getResult();
    }
}
