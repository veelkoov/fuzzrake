<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Entity\ArtisanCommissionsStatus;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tests\TestUtils\Cases\TestCase;
use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\OfferStatus\OfferStatusProcessor;
use App\Tracking\StatusTracker;
use App\Tracking\TextParser;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Response;

/**
 * @small
 */
class StatusTrackerTest extends TestCase
{
    public function testNoCommissionsUrlsResetsEverything(): void
    {
        $artisan = Artisan::new()
            ->setCsTrackerIssue(true) // We had error marker before
            ->addCommission(new ArtisanCommissionsStatus()) // There was some status
            ->setCommissionsUrls(''); // Artisan removed tracking
        $artisan->getVolatileData()
            ->setLastCsUpdate(new DateTimeImmutable('1 day ago')); // We had some check previously

        $testSubject = $this->getTestSubject($artisan, []);
        $testSubject->performUpdates();

        self::assertFalse($artisan->getCsTrackerIssue()); // The error marker is removed
        self::assertOfferStatuses([], $artisan); // Offers got cleared
        self::assertNull($artisan->getCsLastCheck()); // Last CS check reset to null
    }

    public function testCheckSetsLastCsTimestamp(): void
    {
        $artisan = Artisan::new()
            ->setCommissionsUrls('one-url'); // There is a single URL to check

        $testSubject = $this->getTestSubject($artisan, [
            [], // We get empty analysis result (should not matter for the timestamp)
        ]);

        $testSubject->performUpdates();

        self::assertNotNull($artisan->getCsLastCheck()); // The last CS timestamp got set
        self::assertInstanceOf(DateTimeImmutable::class, $artisan->getCsLastCheck());
    }

    public function testSuccessfulCheckResetsErrorState(): void
    {
        $artisan = Artisan::new()
            ->setCsTrackerIssue(true) // There was an error marker
            ->setCommissionsUrls('one-url'); // There is a single URL to check

        $os1 = new OfferStatus('Some-offer', true);

        $testSubject = $this->getTestSubject($artisan, [
            [$os1], // We will find one offer status
        ]);

        $testSubject->performUpdates();

        self::assertFalse($artisan->getCsTrackerIssue()); // The error maker got cleared
        self::assertOfferStatuses([$os1], $artisan); // The parsed status is available
    }

    public function testEmptyResultFromSingleUrlSetsErrorState(): void
    {
        $artisan = Artisan::new()
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrls('one-url'); // We check a single URL

        $testSubject = $this->getTestSubject($artisan, [
            [], // We get empty analysis result
        ]);

        $testSubject->performUpdates();

        self::assertTrue($artisan->getCsTrackerIssue()); // There is an error marker now
        self::assertEmpty($artisan->getCommissions()); // Result expected empty
    }

    public function testSingleFailedCommissionsUrlSetsErrorState(): void
    {
        $artisan = Artisan::new()
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrls("one-url\nsecond-url"); // There are two tracked URLs

        $testSubject = $this->getTestSubject($artisan, [
            [new OfferStatus('Some-offer', true)], // First URL brought some results
            [], // But the second one returned empty set
        ]);

        $testSubject->performUpdates();

        self::assertTrue($artisan->getCsTrackerIssue()); // There is an error marker now
        self::assertCount(1, $artisan->getCommissions()); // The status parsed in the first URL is available
    }

    public function testResultsFromTwoUrlsAreProperlyGathered(): void
    {
        $artisan = Artisan::new()
            ->setCsTrackerIssue(true) // There was an error marker before
            ->setCommissionsUrls("one-url\nsecond-url"); // There are two tracked URLs

        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', true);
        $os3 = new OfferStatus('Next-offer', false);

        $testSubject = $this->getTestSubject($artisan, [
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);

        $testSubject->performUpdates();

        self::assertFalse($artisan->getCsTrackerIssue()); // The error maker is gone
        self::assertOfferStatuses([$os1, $os2, $os3], $artisan); // All statuses parsed are available
    }

    public function testContradictingOfferStatusesRemoveTheOfferAndSetsErrorState(): void
    {
        $artisan = Artisan::new()
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrls("one-url\nsecond-url"); // There are two tracked URLs

        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', true);
        $os3 = new OfferStatus('Some-offer', false); // Different than $os1

        $testSubject = $this->getTestSubject($artisan, [
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);

        $testSubject->performUpdates();

        self::assertTrue($artisan->getCsTrackerIssue()); // The error maker is now set
        self::assertOfferStatuses([$os2], $artisan); // Only the not-conflicting status is available
    }

    public function testDuplicatedOfferStatusesKeepTheOfferButSetsErrorState(): void
    {
        $artisan = Artisan::new()
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrls("one-url\nsecond-url"); // There are two tracked URLs

        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', false);
        $os3 = new OfferStatus('Some-offer', true); // Same as $os1

        $testSubject = $this->getTestSubject($artisan, [
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);

        $testSubject->performUpdates();

        self::assertTrue($artisan->getCsTrackerIssue()); // The error maker is now set
        self::assertOfferStatuses([$os1, $os2], $artisan); // Only the not-conflicting status is available
    }

    /**
     * @param array<array<OfferStatus>> $mockReturnedOfferStatuses
     */
    private function getTestSubject(Artisan $artisan, array $mockReturnedOfferStatuses): StatusTracker
    {
        $mockedUrlsCount = count($mockReturnedOfferStatuses);

        $artisanRepoMock = self::createMock(ArtisanRepository::class);
        $artisanRepoMock->expects(self::once())->method('findAll')->willReturn([$artisan->getArtisan()]);

        $loggerMock = self::createMock(LoggerInterface::class);

        $urlsFetchDateTime = UtcClock::now();
        $dummyWebpageSnapshot = new Snapshot('', '', $urlsFetchDateTime, '', Response::HTTP_OK, [], []);
        foreach ($artisan->getUrls() as $url) {
            $url->getState()->setLastSuccessUtc($urlsFetchDateTime);
        }

        $snapshotsMock = self::createMock(WebpageSnapshotManager::class);
        $snapshotsMock
            ->expects(self::exactly($mockedUrlsCount))
            ->method('get')
            ->willReturn($dummyWebpageSnapshot);

        $parserMock = self::createMock(TextParser::class);
        $parserMock
            ->expects(self::exactly($mockedUrlsCount))
            ->method('getOfferStatuses')
            ->willReturnOnConsecutiveCalls(...$mockReturnedOfferStatuses);

        $emMock = $this->createMock(EntityManagerInterface::class);

        $processor = new OfferStatusProcessor($parserMock);
        $ioMock = $this->createMock(SymfonyStyle::class);

        return new StatusTracker($loggerMock, $emMock, $artisanRepoMock, $processor, $snapshotsMock, false, $ioMock);
    }

    /**
     * @param OfferStatus[] $expected
     */
    private static function assertOfferStatuses(array $expected, Artisan $actualArtisan): void
    {
        self::assertCount(count($expected), $actualArtisan->getCommissions());

        $actual = $actualArtisan->getCommissions()->toArray();

        foreach ($actual as $a_key       => $commission) {
            foreach ($expected as $e_key => $expectedOfferStatus) {
                if ($commission->getOffer() === $expectedOfferStatus->offer
                    && $commission->getIsOpen() === $expectedOfferStatus->status) {
                    unset($expected[$e_key]);
                    unset($actual[$a_key]);

                    break;
                }
            }
        }

        self::assertEmpty($actual, 'There were unexpected, additional commissions statuses');
        self::assertEmpty($expected, 'Some commissions statuses were missing');
    }
}
