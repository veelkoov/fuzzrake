<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tracking\AnalysisAggregator;
use App\Tracking\CreatorTracker;
use App\Tracking\CreatorUpdater;
use App\Tracking\Data\AnalysisInput;
use App\Tracking\Data\AnalysisResult;
use App\Tracking\Data\AnalysisResults;
use App\Tracking\TextProcessing\SnapshotProcessor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\SnapshotsManager;
use App\Utils\Web\Url\Url;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Throwable;
use Veelkoov\Debris\StringList;

#[Small]
class CreatorTrackerTest extends FuzzrakeTestCase
{
    private CreatorTracker $subject;
    private SnapshotsManager&MockObject $snapshotsManagerMock;
    private SnapshotProcessor&MockObject $snapshotProcessorMock;
    private AnalysisAggregator&MockObject $analysisAggregatorMock;
    private CreatorUpdater&MockObject $creatorUpdaterMock;

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        $this->snapshotsManagerMock = self::createMock(SnapshotsManager::class);
        $this->snapshotProcessorMock = self::createMock(SnapshotProcessor::class);
        $this->analysisAggregatorMock = self::createMock(AnalysisAggregator::class);
        $this->creatorUpdaterMock = self::createMock(CreatorUpdater::class);

        $this->subject = new CreatorTracker(
            self::createStub(LoggerInterface::class),
            $this->snapshotsManagerMock,
            $this->snapshotProcessorMock,
            $this->analysisAggregatorMock,
            $this->creatorUpdaterMock,
        );
    }

    public function testChangesNotAppliedOnFailureAndRetryPossible(): void
    {
        $creator = new Creator()->setCreatorId('TEST001')->setCommissionsUrls(['']);

        $analysisResults = new AnalysisResults(new StringList(), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::never())->method('applyResults');

        $result = $this->subject->track($creator, true, true);

        self::assertFalse($result);
    }

    public function testChangesAppliedOnFailureWithoutRetryPossibility(): void
    {
        $creator = new Creator()->setCreatorId('TEST001')->setCommissionsUrls(['']);

        $analysisResults = new AnalysisResults(new StringList(), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::once())->method('applyResults')->with($creator, $analysisResults);

        $result = $this->subject->track($creator, false, true);

        self::assertFalse($result);
    }

    public function testChangesAppliedOnEvenPartialSuccess(): void
    {
        $creator = new Creator()->setCreatorId('TEST001')->setCommissionsUrls(['']);

        $analysisResults = new AnalysisResults(new StringList(['Pancakes']), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::once())->method('applyResults')->with($creator, $analysisResults);

        $result = $this->subject->track($creator, true, true);

        self::assertTrue($result);
    }

    public function testEverythingGetsResetWhenThereAreNoTrackedUrls(): void
    {
        $creator = new Creator()->setCreatorId('TEST001')
            ->setOpenFor(['something'])->setClosedFor(['anything'])
            ->setCsLastCheck(UtcClock::now())->setCsTrackerIssue(true);

        $result = $this->subject->track($creator, true, true);

        self::assertTrue($result);
        self::assertFalse($creator->getCsTrackerIssue());
        self::assertNull($creator->getCsLastCheck());
        self::assertEmpty($creator->getOpenFor());
        self::assertEmpty($creator->getClosedFor());
    }

    public function testAllTrackedUrlsAreQueriedAndProcessed(): void
    {
        $creator = new Creator()->setCreatorId('TEST001')
            ->setCommissionsUrls(['https://example.com/url1', 'https://example.com/url2']);

        // For each of the URL, return a snapshot with contents equaling to the URL
        $this->snapshotsManagerMock->expects($this->exactly(2))->method('get')
            ->willReturnCallback(static fn (Url $url, bool $refetch) => self::getSnapshot($url->getUrl()));

        // For each of the snapshots, return analysis stating it's open for whatever the content is.
        // Resulting in open for: URL, closed for: nothing, no issues.
        $this->snapshotProcessorMock->expects($this->exactly(2))->method('process')
            ->willReturnCallback(static fn (AnalysisInput $input) => new AnalysisResult($input->url->getUrl(),
                StringList::of($input->url->getUrl()), StringList::of(), false));

        // Aggregator should now retrieve two results with URLs
        $this->analysisAggregatorMock->expects($this->once())->method('aggregate')
            ->willReturnCallback(function (Creator $creator, array $results): AnalysisResults {
                self::assertCount(2, $results);

                self::assertInstanceOf(AnalysisResult::class, $results[0]);
                self::assertSame('https://example.com/url1', $results[0]->url);
                self::assertSame('https://example.com/url1', $results[0]->openFor->single());
                self::assertEmpty($results[0]->closedFor);
                self::assertFalse($results[0]->hasEncounteredIssues);

                self::assertInstanceOf(AnalysisResult::class, $results[1]);
                self::assertSame('https://example.com/url2', $results[1]->url);
                self::assertSame('https://example.com/url2', $results[1]->openFor->single());
                self::assertEmpty($results[1]->closedFor);
                self::assertFalse($results[1]->hasEncounteredIssues);

                return new AnalysisResults(StringList::of('Success'), StringList::of(), false);
            });

        $result = $this->subject->track($creator, true, false);

        self::assertTrue($result);
    }
}
