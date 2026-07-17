<?php

declare(strict_types=1);

namespace App\Tests\Reviews;

use App\Entity\Post;
use App\Entity\Submission;
use App\Entity\TopicRead;
use App\Reviews\SubmissionTopicService;
use App\Security\Role;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tests\TestUtils\TestUser;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Medium]
class SubmissionTopicServiceTest extends FuzzrakeKernelTestCase
{
    use ClockSensitiveTrait;

    public function testGetUnreadCountsEmpty(): void
    {
        $reviewer = TestUser::get(roles: [Role::REVIEWER]);
        self::persistAndFlush($reviewer);

        $subject = self::getContainerService(SubmissionTopicService::class);
        $result = $subject->getUnreadCounts($reviewer, []);

        self::assertEmpty($result);
    }

    #[DataProvider('getUnreadCountsOwnTopicsDataProvider')]
    public function testGetUnreadCountsOwnTopics(?bool $topicPostRead, bool $topicPostOwned, int $expected): void
    {
        self::mockTime();

        $reviewer = TestUser::get(roles: [Role::REVIEWER]);
        $otherReviewer = TestUser::get(roles: [Role::REVIEWER]);

        $submission = new Submission(false);

        $topic = new Post($topicPostOwned ? $reviewer : $otherReviewer, $submission);

        if (true === $topicPostRead) {
            $topicRead = new TopicRead($reviewer, $topic)->setLastRead(UtcClock::at('+5 minutes'));
        } elseif (false === $topicPostRead) {
            $topicRead = new TopicRead($reviewer, $topic)->setLastRead(UtcClock::at('-5 minutes'));
        } else {
            $topicRead = null;
        }

        $response = new Post($otherReviewer, $submission, $topic)->setEditedUtc(UtcClock::at('+10 minutes'));

        if (null !== $topicRead) {
            self::persist($topicRead);
        }
        self::persistAndFlush($reviewer, $otherReviewer, $submission, $topic, $response);

        $subject = self::getContainerService(SubmissionTopicService::class);
        $result = $subject->getUnreadCounts($reviewer, [$submission]);

        self::assertSame([(int) $submission->getId() => $expected], $result->toArray());
    }

    public static function getUnreadCountsOwnTopicsDataProvider(): iterable
    {
        return [
            // Topic post read | Topic post owned | Expected unread count
            [null, true, 1],
            [null, false, 2],
            [false, true, 1],
            [false, false, 2],
            [true, true, 1],
            [true, false, 1],
        ];
    }

    public function testRegressionOnlyOwnTopicReadsCount(): void
    {
        self::mockTime();

        $reviewer1 = TestUser::get(roles: [Role::REVIEWER]);
        $reviewer2 = TestUser::get(roles: [Role::REVIEWER]);

        $submission = new Submission(false);

        $topic = new Post($reviewer1, $submission);
        $topicRead = new TopicRead($reviewer1, $topic)->setLastRead(UtcClock::at('+1 minute'));

        self::persistAndFlush($reviewer1, $reviewer2, $submission, $topic, $topicRead);

        $subject = self::getContainerService(SubmissionTopicService::class);
        $result = $subject->getUnreadCounts($reviewer2, [$submission]);

        self::assertSame([(int) $submission->getId() => 1], $result->toArray());
    }
}
