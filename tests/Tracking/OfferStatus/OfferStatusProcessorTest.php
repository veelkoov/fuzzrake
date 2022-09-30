<?php

declare(strict_types=1);

namespace App\Tests\Tracking\OfferStatus;

use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\OfferStatus\OfferStatusProcessor;
use App\Tracking\OfferStatus\OfferStatusResult;
use App\Tracking\TextParser;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\TestCase;

class OfferStatusProcessorTest extends TestCase
{
    public function testNoCommissionsUrlsResetsEverything(): void
    {
        $testSubject = $this->getTestSubject([]);
        $result = $testSubject->getOfferStatuses([]);

        self::assertFalse($result->csTrackerIssue); // No error marker
        self::assertOfferStatuses([], $result); // No offers
        self::assertNull($result->lastCsUpdate); // Null last CS check
    }

    public function testCheckingAnyUrlSetsLastCsTimestamp(): void
    {
        $testSubject = $this->getTestSubject([
            [], // We get empty analysis result (should not matter for the timestamp)
        ]);
        $result = $testSubject->getOfferStatuses($this->getEmptySnapshots(1));

        self::assertNotNull($result->lastCsUpdate);
    }

    public function testSuccessfulCheckResetsErrorState(): void
    {
        $os1 = new OfferStatus('Some-offer', true); // Checking single URL

        $testSubject = $this->getTestSubject([
            [$os1], // We will find one offer status
        ]);
        $result = $testSubject->getOfferStatuses($this->getEmptySnapshots(1));

        self::assertFalse($result->csTrackerIssue);
        self::assertOfferStatuses([$os1], $result);
    }

    public function testEmptyResultFromSingleUrlSetsErrorState(): void
    {
        $testSubject = $this->getTestSubject([
            [], // We get empty analysis result
        ]);

        $result = $testSubject->getOfferStatuses($this->getEmptySnapshots(1));

        self::assertTrue($result->csTrackerIssue);
        self::assertEmpty($result->offerStatuses);
    }

    public function testSingleFailedCommissionsUrlSetsErrorState(): void
    {
        $testSubject = $this->getTestSubject([
            [new OfferStatus('Some-offer', true)], // First URL brought some results
            [], // But the second one returned empty set
        ]);

        $result = $testSubject->getOfferStatuses($this->getEmptySnapshots(2));

        self::assertTrue($result->csTrackerIssue);
        self::assertCount(1, $result->offerStatuses); // The status parsed in the first URL is available
    }

    public function testResultsFromTwoUrlsAreProperlyGathered(): void
    {
        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', true);
        $os3 = new OfferStatus('Next-offer', false);

        $testSubject = $this->getTestSubject([
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);

        $result = $testSubject->getOfferStatuses($this->getEmptySnapshots(2));

        self::assertFalse($result->csTrackerIssue);
        self::assertOfferStatuses([$os1, $os2, $os3], $result); // All statuses parsed are available
    }

    public function testContradictingOfferStatusesRemoveTheOfferAndSetsErrorState(): void
    {
        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', true);
        $os3 = new OfferStatus('Some-offer', false); // Different than $os1

        $testSubject = $this->getTestSubject([
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);

        $result = $testSubject->getOfferStatuses($this->getEmptySnapshots(2));

        self::assertTrue($result->csTrackerIssue);
        self::assertOfferStatuses([$os2], $result); // Only the not-conflicting status is available
    }

    public function testDuplicatedOfferStatusesKeepTheOfferButSetsErrorState(): void
    {
        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', false);
        $os3 = new OfferStatus('Some-offer', true); // Same as $os1

        $testSubject = $this->getTestSubject([
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);

        $result = $testSubject->getOfferStatuses($this->getEmptySnapshots(2));

        self::assertTrue($result->csTrackerIssue); // The error maker is now set
        self::assertOfferStatuses([$os1, $os2], $result); // Only the not-conflicting status is available
    }

    /**
     * @param array<array<OfferStatus>> $mockReturnedOfferStatuses
     */
    private function getTestSubject(array $mockReturnedOfferStatuses): OfferStatusProcessor
    {
        $mockedUrlsCount = count($mockReturnedOfferStatuses);

        $parserMock = self::createMock(TextParser::class);
        $parserMock
            ->expects(self::exactly($mockedUrlsCount))
            ->method('getOfferStatuses')
            ->willReturnOnConsecutiveCalls(...$mockReturnedOfferStatuses);

        return new OfferStatusProcessor($parserMock);
    }

    /**
     * @param OfferStatus[] $expected
     */
    private static function assertOfferStatuses(array $expected, OfferStatusResult $result): void
    {
        self::assertCount(count($expected), $result->offerStatuses);

        $actual = $result->offerStatuses;

        foreach ($actual as $a_key       => $commission) {
            foreach ($expected as $e_key => $expectedOfferStatus) {
                if ($commission->offer === $expectedOfferStatus->offer
                    && $commission->status === $expectedOfferStatus->status) {
                    unset($expected[$e_key]);
                    unset($actual[$a_key]);

                    break;
                }
            }
        }

        self::assertEmpty($actual, 'There were unexpected, additional commissions statuses');
        self::assertEmpty($expected, 'Some commissions statuses were missing');
    }

    /**
     * @return Snapshot[]
     */
    private function getEmptySnapshots(int $count): array
    {
        $result = [];

        while ($count--) {
            $result[] = new Snapshot('', '', UtcClock::now(), '', 200, [], []);
        }

        return $result;
    }
}
