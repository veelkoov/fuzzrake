<?php

declare(strict_types=1);

namespace App\Tests\Tasks\TrackerUpdates\Commissions;

use App\Entity\Artisan;
use App\Entity\ArtisanCommissionsStatus;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\Commissions\CommissionsTrackerTask;
use App\Utils\Data\ArtisanChanges;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\Tracking\CommissionsStatusParser;
use App\Utils\Tracking\OfferStatus;
use App\Utils\Web\Snapshot\WebpageSnapshot;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class CommissionsTrackerTaskTest extends TestCase
{
    public function testNoCommissionsUrlResetsErrorAndOffers(): void
    {
        $inputArtisan = (new Artisan())
            ->setCsTrackerIssue(true) // We had error marker before
            ->addCommission(new ArtisanCommissionsStatus()) // There was some status
            ->setCommissionsUrl(''); // Artisan removed tracking

        $testSubject = $this->getTestSubject($inputArtisan, []);
        $testResult = $testSubject->getUpdates();

        $changedArtisan = $this->getChangedArtisan($testResult);

        self::assertFalse($changedArtisan->getCsTrackerIssue()); // The error marker is removed
        self::assertOfferStatuses([], $changedArtisan); // Offers got cleared
    }

    public function testSuccessfulCheckResetsErrorState(): void
    {
        $inputArtisan = (new Artisan())
            ->setCsTrackerIssue(true) // There was an error marker
            ->setCommissionsUrl('one-url'); // There is a single URL to check

        $os1 = new OfferStatus('Some-offer', true);

        $testSubject = $this->getTestSubject($inputArtisan, [
            [$os1], // We will find one offer status
        ]);
        $testResult = $testSubject->getUpdates();

        $changedArtisan = $this->getChangedArtisan($testResult);

        self::assertFalse($changedArtisan->getCsTrackerIssue()); // The error maker got cleared
        self::assertOfferStatuses([$os1], $changedArtisan); // The parsed status is available
    }

    public function testEmptyResultFromSingleUrlSetsErrorState(): void
    {
        $inputArtisan = (new Artisan())
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrl('one-url'); // We check a single URL

        $testSubject = $this->getTestSubject($inputArtisan, [
            [], // We get empty analysis result
        ]);
        $testResult = $testSubject->getUpdates();

        $changedArtisan = $this->getChangedArtisan($testResult);

        self::assertTrue($changedArtisan->getCsTrackerIssue()); // There is an error marker now
        self::assertEmpty($changedArtisan->getCommissions()); // Result expected empty
    }

    public function testSingleFailedCommissionsUrlSetsErrorState(): void
    {
        $inputArtisan = (new Artisan())
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrl("one-url\nsecond-url"); // There are two tracked URLs

        $testSubject = $this->getTestSubject($inputArtisan, [
            [new OfferStatus('Some-offer', true)], // First URL brought some results
            [], // But the second one returned empty set
        ]);
        $testResult = $testSubject->getUpdates();

        $changedArtisan = $this->getChangedArtisan($testResult);

        self::assertTrue($changedArtisan->getCsTrackerIssue()); // There is an error marker now
        self::assertCount(1, $changedArtisan->getCommissions()); // The status parsed in the first URL is available
    }

    public function testResultsFromTwoUrlsAreProperlyGathered(): void
    {
        $inputArtisan = (new Artisan())
            ->setCsTrackerIssue(true) // There was an error marker before
            ->setCommissionsUrl("one-url\nsecond-url"); // There are two tracked URLs

        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', true);
        $os3 = new OfferStatus('Next-offer', false);

        $testSubject = $this->getTestSubject($inputArtisan, [
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);
        $testResult = $testSubject->getUpdates();

        $changedArtisan = $this->getChangedArtisan($testResult);

        self::assertFalse($changedArtisan->getCsTrackerIssue()); // The error maker is gone
        self::assertOfferStatuses([$os1, $os2, $os3], $changedArtisan); // All statuses parsed are available
    }

    public function testContradictingOfferStatusesRemoveTheOfferAndSetsErrorState(): void
    {
        $inputArtisan = (new Artisan())
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrl("one-url\nsecond-url"); // There are two tracked URLs

        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', true);
        $os3 = new OfferStatus('Some-offer', false); // Different than $os1

        $testSubject = $this->getTestSubject($inputArtisan, [
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);
        $testResult = $testSubject->getUpdates();

        $changedArtisan = $this->getChangedArtisan($testResult);

        self::assertTrue($changedArtisan->getCsTrackerIssue()); // The error maker is now set
        self::assertOfferStatuses([$os2], $changedArtisan); // Only the not-conflicting status is available
    }

    public function testDuplicatedOfferStatusesKeepTheOfferButSetsErrorState(): void
    {
        $inputArtisan = (new Artisan())
            ->setCsTrackerIssue(false) // There was no error marker before
            ->setCommissionsUrl("one-url\nsecond-url"); // There are two tracked URLs

        $os1 = new OfferStatus('Some-offer', true);
        $os2 = new OfferStatus('Another-offer', false);
        $os3 = new OfferStatus('Some-offer', true); // Same as $os1

        $testSubject = $this->getTestSubject($inputArtisan, [
            [$os1], [$os2, $os3], // We get results from both URLs
        ]);
        $testResult = $testSubject->getUpdates();

        $changedArtisan = $this->getChangedArtisan($testResult);

        self::assertTrue($changedArtisan->getCsTrackerIssue()); // The error maker is now set
        self::assertOfferStatuses([$os1, $os2], $changedArtisan); // Only the not-conflicting status is available
    }

    private function getTestSubject(Artisan $artisan, array $mockReturnedOfferStatuses): CommissionsTrackerTask
    {
        $mockedUrlsCount = count($mockReturnedOfferStatuses);

        $artisanRepoMock = self::createMock(ArtisanRepository::class);
        $artisanRepoMock->expects(self::once())->method('findAll')->willReturn([$artisan]);

        $loggerMock = self::createMock(LoggerInterface::class);

        $dummyWebpageSnapshot = new WebpageSnapshot('', '', DateTimeUtils::getNowUtc(), '', Response::HTTP_OK, []);

        $snapshotsMock = self::createMock(WebpageSnapshotManager::class);
        $snapshotsMock
            ->expects(self::exactly($mockedUrlsCount))
            ->method('get')
            ->willReturn($dummyWebpageSnapshot);

        $parserMock = self::createMock(CommissionsStatusParser::class);
        $parserMock
            ->expects(self::exactly($mockedUrlsCount))
            ->method('getCommissionsStatuses')
            ->willReturnOnConsecutiveCalls(...$mockReturnedOfferStatuses);

        return new CommissionsTrackerTask($artisanRepoMock, $loggerMock, $snapshotsMock, $parserMock);
    }

    /**
     * @param ArtisanChanges[] $testResult
     */
    private function getChangedArtisan(array $testResult): Artisan
    {
        self::assertCount(1, $testResult);

        $artisanChanges = array_pop($testResult);

        return $artisanChanges->getChanged();
    }

    /**
     * @param OfferStatus[] $expected
     */
    private static function assertOfferStatuses(array $expected, Artisan $actualArtisan): void
    {
        self::assertCount(count($expected), $actualArtisan->getCommissions());

        $actual = $actualArtisan->getCommissions()->toArray();

        foreach ($actual as $a_key => $commission) {
            foreach ($expected as $e_key => $expectedOfferStatus) {
                if ($commission->getOffer() === $expectedOfferStatus->getOffer()
                    && $commission->getIsOpen() === $expectedOfferStatus->getStatus()) {
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