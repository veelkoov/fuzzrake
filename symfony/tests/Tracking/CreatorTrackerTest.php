<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tracking\AnalysisAggregator;
use App\Tracking\AnalysisResult;
use App\Tracking\AnalysisResults;
use App\Tracking\CreatorTracker;
use App\Tracking\CreatorUpdater;
use App\Tracking\SnapshotProcessor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
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
        $creator = new Creator()->setCreatorId('TEST001');

        $analysisResults = new AnalysisResults(new StringList(), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::never())->method('applyResults');

        $this->subject->update($creator, true, true);
    }

    public function testChangesAppliedOnFailureWithoutRetryPossibility(): void
    {
        $creator = new Creator()->setCreatorId('TEST001');

        $analysisResults = new AnalysisResults(new StringList(), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::once())->method('applyResults')->with($creator, $analysisResults);

        $this->subject->update($creator, false, true);
    }

    public function testChangesAppliedEvenOnPartialSuccess(): void
    {
        $creator = new Creator()->setCreatorId('TEST001');

        $analysisResults = new AnalysisResults(new StringList(['Pancakes']), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::once())->method('applyResults')->with($creator, $analysisResults);

        $this->subject->update($creator, true, true);
    }

    public function testAllRetrievedSnapshotAnalysedAndResultsAggregated(): void
    {
        $creator = new Creator()
            ->setCreatorId('TEST001')
            ->setCommissionsUrls(['https://getfursu.it/', 'https://getfursu.it/info'])
        ;

        $this->snapshotsManagerMock
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(static fn (Url $url) => new Snapshot('', new SnapshotMetadata($url->getUrl(),
                UtcClock::now(), 200, [], [])));

        $analysisResult = new AnalysisResult('', new StringList(), new StringList(), false); // FIXME

        $this->snapshotProcessorMock
            ->expects(self::exactly(2))
            ->method('analyse')
            ->willReturnCallback(static fn (Snapshot $snapshot) => $analysisResult);

        $this->analysisAggregatorMock
            ->expects(self::once())
            ->method('aggregate')
            ->with([$analysisResult, $analysisResult])
            ->willReturn(new AnalysisResults(new StringList(), new StringList(), false));

        $this->subject->update($creator, true, true);
    }
}
