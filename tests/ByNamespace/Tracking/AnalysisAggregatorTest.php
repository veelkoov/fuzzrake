<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Tracking;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tests\TestUtils\UserCreator;
use App\Tracking\AnalysisAggregator;
use App\Tracking\Data\AnalysisResult;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use Psr\Log\LoggerInterface;
use Veelkoov\Debris\Vecs\StringVec;

#[Small]
class AnalysisAggregatorTest extends FuzzrakeTestCase
{
    private AnalysisAggregator $subject;
    private Creator $creator;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new AnalysisAggregator(self::createStub(LoggerInterface::class));
        $this->creator = UserCreator::get()->setCreatorId('TEST001');
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
        $openFor = StringVec::of('Commissions');

        $result = $this->subject->aggregate($this->creator, array_map(
            static fn (bool $encountered) => new AnalysisResult('', $openFor, new StringVec(), $encountered),
            $encounteredIssuesArray,
        ));

        self::assertSame($expected, $result->hasEncounteredIssues);
    }

    public function testSingleUrlOneOffer(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions'), StringVec::of(), false),
        ]);

        self::assertSameItems(['Commissions'], $result->openFor);
        self::assertSameItems([], $result->closedFor);
        self::assertFalse($result->hasEncounteredIssues);
    }

    public function testSingleUrlNoOffer(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of(), StringVec::of(), false),
        ]);

        self::assertSameItems([], $result->openFor);
        self::assertSameItems([], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testSingleEmptyUrlAnalysisSetsErrorStatus(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', new StringVec(), new StringVec(), false),
            new AnalysisResult('', new StringVec(), StringVec::of('Quotes'), false),
        ]);

        self::assertSameItems([], $result->openFor);
        self::assertSameItems(['Quotes'], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testResultsFromMultipleUrlsAreProperlyAggregated(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions', 'Partials'), StringVec::of('Parts'), false),
            new AnalysisResult('', StringVec::of('Mini partials'), StringVec::of('Quotes'), false),
        ]);

        self::assertSameItems(['Commissions', 'Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems(['Parts', 'Quotes'], $result->closedFor);
        self::assertFalse($result->hasEncounteredIssues);
    }

    public function testContradictingOfferStatusesInASingleUrlCancelEachOtherAndSetErrorState(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions', 'Partials'), StringVec::of('Commissions'), false),
            new AnalysisResult('', StringVec::of('Mini partials'), new StringVec(), false),
        ]);

        self::assertSameItems(['Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems([], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testContradictingOfferStatusesInASingleUrlSetErrorStateButDoesntCancelOtherUrlsDuplicate(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions', 'Partials'), StringVec::of('Commissions'), false),
            new AnalysisResult('', StringVec::of('Mini partials'), StringVec::of('Commissions'), false),
        ]);

        self::assertSameItems(['Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems(['Commissions'], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testContradictingOffersFromDifferentUrlsAreRemovedSettingErrorState(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions', 'Partials'), StringVec::of('Parts', 'Mini partials'), false),
            new AnalysisResult('', StringVec::of('Mini partials'), StringVec::of('Commissions', 'Quotes'), false),
        ]);

        self::assertSameItems(['Partials'], $result->openFor);
        self::assertSameItems(['Parts', 'Quotes'], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testMultipleContradictingOffersOnASingleUrlCancelEachOtherSettingErrorState(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions', 'Commissions'), StringVec::of('Commissions'), false),
        ]);

        self::assertSameItems([], $result->openFor);
        self::assertSameItems([], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testDuplicatedOfferStatusInASingleUrlSetsErrorStateButIsNotCancelled(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions', 'Partials', 'Commissions'), new StringVec(), false),
            new AnalysisResult('', StringVec::of('Mini partials'), new StringVec(), false),
        ]);

        self::assertSameItems(['Commissions', 'Partials', 'Mini partials'], $result->openFor);
        self::assertSameItems([], $result->closedFor);
        self::assertTrue($result->hasEncounteredIssues);
    }

    public function testDuplicatedOfferStatusInDifferentUrlIsOk(): void
    {
        $result = $this->subject->aggregate($this->creator, [
            new AnalysisResult('', StringVec::of('Commissions', 'Partials'), StringVec::of('Mini partials'), false),
            new AnalysisResult('', StringVec::of('Commissions'), StringVec::of('Heads', 'Mini partials'), false),
        ]);

        self::assertSameItems(['Commissions', 'Partials'], $result->openFor);
        self::assertSameItems(['Heads', 'Mini partials'], $result->closedFor);
        self::assertFalse($result->hasEncounteredIssues);
    }
}
