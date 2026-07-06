<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Reviews;

use App\Entity\Post;
use App\Entity\Submission;
use App\Reviews\DailySubmissionPostsSummary;
use App\Reviews\SubmissionActivityService;
use App\Security\Role;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tests\TestUtils\TestUser;
use App\Tests\TestUtils\UserCreator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Medium]
class SubmissionActivityServiceTest extends FuzzrakeKernelTestCase
{
    use ClockSensitiveTrait;

    /**
     * @throws DateTimeException
     */
    #[Test]
    public function getWeeklyPostsSummariesSmokeTest(): void
    {
        self::mockTime('2026-01-01T00:00:00'); // Time in the past for the submission to have sane times

        $creator1 = UserCreator::get(true);
        $submission1 = new Submission(false)->setCreator($creator1->entity);
        $creator2 = UserCreator::get(true);
        $submission2 = new Submission(false)->setCreator($creator2->entity);

        $reviewer1 = TestUser::get(true, roles: [Role::REVIEWER])->setNickname('Judy');
        $reviewer2 = TestUser::get(true, roles: [Role::REVIEWER])->setNickname('Nick');

        $now = '2026-06-10T05:05:05'; // Summary should contain 3rd midnight to 10th, "now"

        // Posted longer than 7 days ago, will be skipped
        self::mockTime('2026-06-01T02:59:59');
        $post1 = new Post($reviewer1, $submission1);

        // Posted over 7 days ago, but edited to fit
        self::mockTime('2026-06-02T01:00:00');
        $post2 = new Post($reviewer1, $submission1)->setEditedUtc(UtcClock::at('2026-06-03T00:00:00'));

        // Posted in the period
        self::mockTime('2026-06-03T01:00:00');
        $post3 = new Post($reviewer2, $submission1);

        // Posted in the period, second to the same submission
        self::mockTime('2026-06-03T01:05:00');
        $post3_2 = new Post($reviewer2, $submission1);

        // Posted in the period
        self::mockTime('2026-06-03T01:00:00');
        $post4 = new Post($reviewer2, $submission2);

        // Posted recently, edited just now
        self::mockTime('2026-06-10T05:00:00');
        $post5 = new Post($reviewer1, $submission2)->setEditedUtc(UtcClock::at('2026-06-10T05:05:04'));

        self::persistAndFlush($creator1, $submission1, $creator2, $submission2, $reviewer1, $reviewer2,
            $post1, $post2, $post3, $post3_2, $post4, $post5);

        self::mockTime($now);

        $subject = self::getContainerService(SubmissionActivityService::class);
        $result = $subject->getWeeklyPostsSummaries();

        self::assertEquals(['2026-06-10', '2026-06-03'], $result->getKeysArray());
        self::assertCount(2, $result->get('2026-06-03'));
        self::assertCount(1, $result->get('2026-06-10'));

        $activity10sub2 = $result->get('2026-06-10')->single();
        $activity03sub1 = $result->get('2026-06-03')
            ->filter(static fn (DailySubmissionPostsSummary $dsps) => $dsps->submissionId === $submission1->getId())
            ->single();
        $activity03sub2 = $result->get('2026-06-03')
            ->filter(static fn (DailySubmissionPostsSummary $dsps) => $dsps->submissionId === $submission2->getId())
            ->single();

        self::assertSame($submission2->getId(), $activity10sub2->submissionId); // No filtering - make sure
        self::assertSame('Judy', $activity10sub2->nicknames);
        self::assertSame(1, $activity10sub2->count);

        self::assertSame('Judy,Nick', $activity03sub1->nicknames);
        self::assertSame(3, $activity03sub1->count);

        self::assertSame('Nick', $activity03sub2->nicknames);
        self::assertSame(1, $activity03sub2->count);
    }
}
