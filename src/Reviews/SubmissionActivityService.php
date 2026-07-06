<?php

declare(strict_types=1);

namespace App\Reviews;

use App\Utils\DateTime\UtcClock;
use App\Utils\Exceptions\UncheckedException;
use DateException;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Veelkoov\Debris\Maps\Base\DStringMap;
use Veelkoov\Debris\Vecs\Base\DVec;

class SubmissionActivityService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return DStringMap<DVec<DailySubmissionPostsSummary>>
     */
    public function getWeeklyPostsSummaries(): DStringMap
    {
        try {
            $before = UtcClock::now();
            $since = $before->sub(new DateInterval('P7D'))->setTime(0, 0, 0, 0);
        } catch (DateException $exception) {
            throw new UncheckedException($exception);
        }

        /**
         * @var array<array{date: string, nicknames: string, count: int, submission_id: int}> $rows
         */
        $rows = $this->entityManager->createNativeQuery(<<<'SQL'
                SELECT date(coalesce(p.edited_utc, p.posted_utc)) AS date,
                group_concat(distinct nickname) AS nicknames,
                COUNT(*) AS count,
                p.submission_id
            FROM posts AS p
            JOIN users AS u
                ON u.id = p.user_id
            WHERE coalesce(p.edited_utc, p.posted_utc) >= :since
              AND coalesce(p.edited_utc, p.posted_utc) < :before
            GROUP BY date(coalesce(p.edited_utc, p.posted_utc)) || '-' || p.submission_id
            ORDER BY date DESC
        SQL, new ResultSetMapping()
            ->addScalarResult('date', 'date')
            ->addScalarResult('nicknames', 'nicknames')
            ->addScalarResult('count', 'count', 'integer')
            ->addScalarResult('submission_id', 'submission_id', 'integer')
        )->execute([
            'since' => $since->format('Y-m-d H:i:s'),
            'before' => $before->format('Y-m-d H:i:s'),
        ]);

        $result = new DStringMap();

        foreach ($rows as $row) {
            $result->getOrSet($row['date'], static fn () => new DVec())->add(new DailySubmissionPostsSummary($row['date'], $row['nicknames'], $row['count'], $row['submission_id']));
        }

        return $result;
    }
}
