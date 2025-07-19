<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tracking\AnalysisAggregator;
use App\Tracking\AnalysisResult;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use Psr\Log\LoggerInterface;
use Veelkoov\Debris\StringList;

#[Small]
class AnalysisAggregatorTest extends FuzzrakeTestCase
{
    private AnalysisAggregator $subject;
    private Creator $creator;

    protected function setUp(): void
    {
        $this->subject = new AnalysisAggregator(self::createStub(LoggerInterface::class));
        $this->creator = new Creator()->setCreatorId('TEST001');
    }

    /**
     * @return list<array{array<bool>, bool}>
     */
    public static function aggregatingEncounteredIssuesDataProvider(): array
    {
        return [
            [[false, false, false], false],
            [[false, true, false], true],
            [[true, true, true], true],
        ];
    }

    /**
     * @param list<bool> $encounteredIssuesArray
     */
    #[DataProvider('aggregatingEncounteredIssuesDataProvider')]
    public function testAggregatingEncounteredIssues(array $encounteredIssuesArray, bool $expected): void
    {
        /* Some consistent result is required, otherwise errors state would be set for other reasons */
        $openFor = StringList::of('Commissions');

        $result = $this->subject->aggregate($this->creator, array_map(
            static fn (bool $encountered) => new AnalysisResult('', $openFor, new StringList(), $encountered),
            $encounteredIssuesArray,
        ));

        self::assertSame($expected, $result->hasEncounteredIssues);
    }

    public function testEmptyUrlAnalysisSetsErrorStatus(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', new StringList(), new StringList(), false),
            new AnalysisResult('', new StringList(), StringList::of('Quotes'), false),
        ]);

        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testResultsFromMultipleUrlsAreProperlyAggregated(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringList::of('Commissions', 'Partials'), StringList::of('Parts'), false),
            new AnalysisResult('', StringList::of('Mini partials'), StringList::of('Quotes'), false),
        ]);

        self::assertSameItems(['Commissions', 'Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems(['Parts', 'Quotes'], $result->closedFor);
        self::assertFalse($result->hasEncounteredIssues);
    }

    public function testContradictingOfferStatusesInASingleUrlCancelEachOtherAndSetErrorState(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringList::of('Commissions', 'Partials'), StringList::of('Commissions'), false),
            new AnalysisResult('', StringList::of('Mini partials'), new StringList(), false),
        ]);

        self::assertSameItems(['Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems([], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testContradictingOfferStatusesInASingleUrlSetErrorStateButDoesntCancelOtherUrlsDuplicate(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringList::of('Commissions', 'Partials'), StringList::of('Commissions'), false),
            new AnalysisResult('', StringList::of('Mini partials'), StringList::of('Commissions'), false),
        ]);

        self::assertSameItems(['Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems(['Commissions'], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testContradictingOffersFromDifferentUrlsAreRemovedSettingErrorState(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringList::of('Commissions', 'Partials'), StringList::of('Parts', 'Mini partials'), false),
            new AnalysisResult('', StringList::of('Mini partials'), StringList::of('Commissions', 'Quotes'), false),
        ]);

        self::assertSameItems(['Partials'], $result->openFor);
        self::assertSameItems(['Parts', 'Quotes'], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testDuplicatedOfferStatusInASingleUrlSetsErrorStateButIsNotCancelled(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringList::of('Commissions', 'Partials', 'Commissions'), new StringList(), false),
            new AnalysisResult('', StringList::of('Mini partials'), new StringList(), false),
        ]);

        self::assertSameItems(['Commissions', 'Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems([], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testDuplicatedOfferStatusInDifferentUrlIsOk(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringList::of('Commissions', 'Partials'), StringList::of('Mini partials'), false),
            new AnalysisResult('', StringList::of('Commissions'), StringList::of('Heads', 'Mini partials'), false),
        ]);

        self::assertSameItems(['Commissions', 'Partials'], $result->openFor);
        self::assertSameItems(['Heads', 'Mini partials'], $result->closedFor);
        self::assertFalse($result->hasEncounteredIssues);
    }
}
