<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DiscussionTopic;
use App\Entity\Submission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscussionTopic>
 */
class DiscussionTopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscussionTopic::class);
    }

    /**
     * @return DiscussionTopic[]
     */
    public function getSubmissionDiscussions(Submission $submission): array
    {
        return $this->createQueryBuilder('d_dt')
            ->leftJoin('d_dt.comments', 'd_dc')
            ->join('d_dt.user', 'd_u1')
            ->leftJoin('d_dc.user', 'd_u2')
            ->where('d_dt.submission = :submission')
            ->setParameter('submission', $submission)
            ->orderBy('d_dt.postedUtc')
            ->addOrderBy('d_dc.postedUtc')
            ->getQuery()
            ->getResult();
    }
}
