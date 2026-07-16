<?php

declare(strict_types=1);

namespace App\Reviews;

use App\Entity\Post;
use App\Entity\Submission;
use App\Entity\User;
use App\Repository\TopicReadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Veelkoov\Debris\Maps\IntToInt;

class SubmissionTopicService
{
    public function __construct(
        private readonly TopicReadRepository $topicReadRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param list<array{entity: Post, response_form: mixed}>|Post $topics
     */
    public function getPostReadTracker(User $user, array|Post $topics): PostReadTracker
    {
        $topics = is_array($topics) ? array_column($topics, 'entity') : [$topics];

        $topicRead = $this->topicReadRepository->findBy([
            'user' => $user,
            'topic' => $topics,
        ]);

        return new PostReadTracker($topicRead, $user);
    }

    /**
     * @param Submission[] $submissions
     */
    public function getUnreadCounts(User $user, array $submissions): IntToInt
    {
        return IntToInt::fromRows(
            $this->entityManager->createNativeQuery(<<<SQL
                SELECT p.submission_id, COUNT(*) AS unread_count
                FROM (
                    SELECT coalesce(parent_id, id)AS topic_id
                        , coalesce(edited_utc, posted_utc) AS time_utc
                        , submission_id
                    FROM posts
                    WHERE submission_id IN (:submission_ids)
                        AND user_id <> :user_id
                ) AS p
                LEFT JOIN topics_reads AS tr
                    ON tr.topic_id = p.topic_id
                WHERE (tr.last_read IS NULL OR tr.last_read < p.time_utc)
                    AND (tr.user_id IS NULL OR tr.user_id = :user_id)
                GROUP BY submission_id
            SQL, new ResultSetMapping()
                ->addScalarResult('submission_id', 'submission_id')
                ->addScalarResult('unread_count', 'unread_count')
            )
                ->setParameter('user_id', $user->getId())
                ->setParameter('submission_ids', array_map(static fn (Submission $submission) => $submission->getId(), $submissions))
                ->getArrayResult(),
            'submission_id', 'unread_count');
    }
}
