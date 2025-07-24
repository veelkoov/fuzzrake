<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tracking\AnalysisAggregator;
use App\Tracking\CreatorTracker;
use App\Tracking\CreatorUpdater;
use App\Tracking\Data\AnalysisResults;
use App\Tracking\TextProcessing\SnapshotProcessor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Snapshots\SnapshotsManager;
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

        $this->subject->track($creator, true, true);
    }

    public function testChangesAppliedOnFailureWithoutRetryPossibility(): void
    {
        $creator = new Creator()->setCreatorId('TEST001')->setCommissionsUrls(['']);

        $analysisResults = new AnalysisResults(new StringList(), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::once())->method('applyResults')->with($creator, $analysisResults);

        $this->subject->track($creator, false, true);
    }

    public function testChangesAppliedOnEvenPartialSuccess(): void
    {
        $creator = new Creator()->setCreatorId('TEST001')->setCommissionsUrls(['']);

        $analysisResults = new AnalysisResults(new StringList(['Pancakes']), new StringList(), true);

        $this->analysisAggregatorMock->expects(self::once())->method('aggregate')->willReturn($analysisResults);
        $this->creatorUpdaterMock->expects(self::once())->method('applyResults')->with($creator, $analysisResults);

        $this->subject->track($creator, true, true);
    }

    // TODO: "Everything gets reset when there are no tracked URLs"

    // TODO: All tracked URLs are queried and processed
}
