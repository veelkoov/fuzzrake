<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Entity\Event;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Tracking\CreatorUpdater;
use App\Tracking\Data\AnalysisResults;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Throwable;
use Veelkoov\Debris\Lists\StringList;

#[Small]
class CreatorUpdaterTest extends FuzzrakeTestCase
{
    use ClockSensitiveTrait;

    private CreatorUpdater $subject;
    private EntityManagerInterface&MockObject $entityManagerMock;

    /**
     * @throws Throwable
     */
    #[Override]
    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->subject = new CreatorUpdater(
            self::createStub(LoggerInterface::class),
            $this->entityManagerMock,
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testChangesAreApplied(): void
    {
        self::mockTime();

        $creator = new Creator()
            ->setCreatorId('TEST001')
            ->setOpenFor(['Pancakes', 'Pizza'])
            ->setClosedFor(['Cheddar'])
            ->setCsTrackerIssue(false)
        ;

        $analysisResults = new AnalysisResults(
            new StringList(['Pancakes', 'Salmiakki']),
            new StringList(['Cheddar', 'Pizza']),
            true,
        );

        $this->subject->applyResults($creator, $analysisResults);

        self::assertSameItems(['Pancakes', 'Salmiakki'], $creator->getOpenFor());
        self::assertSameItems(['Cheddar', 'Pizza'], $creator->getClosedFor());
        self::assertTrue($creator->getCsTrackerIssue());
        self::assertEquals(UtcClock::now(), $creator->getCsLastCheck());

        UtcClock::sleep(1);

        $analysisResults = new AnalysisResults(
            new StringList(['Pancakes']),
            new StringList(['Pizza', 'Salmiakki']),
            false,
        );

        $this->subject->applyResults($creator, $analysisResults);

        self::assertSameItems(['Pancakes'], $creator->getOpenFor());
        self::assertSameItems(['Salmiakki', 'Pizza'], $creator->getClosedFor());
        self::assertFalse($creator->getCsTrackerIssue());
        self::assertEquals(UtcClock::now(), $creator->getCsLastCheck());
    }

    /**
     * @return list<array{list<string>, list<string>, bool, list<string>, list<string>, bool}>
     */
    public static function eventCreationDataProvider(): array
    {
        // NOTE: Initial state: open for Pancakes, closed for Cheddar, no tracking issues

        return [
            // Now open for: Salmiakki
            [['Pancakes', 'Salmiakki'],          ['Cheddar'], true,  ['Salmiakki'],          [],           true],
            // Now open for: Salmiakki, no longer for: Pancakes
            [['Salmiakki'],                      ['Cheddar'], false, ['Salmiakki'],          ['Pancakes'], true],
            // Now open for: Salmiakki and Pizza
            [['Pancakes', 'Salmiakki', 'Pizza'], ['Cheddar'], true,  ['Salmiakki', 'Pizza'], [],           true],

            // No changes (ignoring tracking issue change)
            [['Pancakes'],                       ['Cheddar'],          true,  [], [], false],
            // No changes (ignoring "closed for" change)
            [['Pancakes'],                       ['Cheddar', 'Pizza'], false, [], [], false],
        ];
    }

    /**
     * @param list<string> $newOpenFor
     * @param list<string> $newClosedFor
     * @param list<string> $nowOpenFor
     * @param list<string> $noLongerOpenFor
     */
    #[DataProvider('eventCreationDataProvider')]
    public function testEventCreation(array $newOpenFor, array $newClosedFor, bool $newCsTrackerIssue,
        array $nowOpenFor, array $noLongerOpenFor, bool $hasEvent): void
    {
        self::mockTime();

        $creator = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Creator w/event test')
            ->setOpenFor(['Pancakes'])
            ->setClosedFor(['Cheddar'])
            ->setCsTrackerIssue(false)
            ->setCommissionsUrls(['https://getfursu.it/', 'https://getfursu.it/info'])
        ;

        $analysisResults = new AnalysisResults(
            new StringList($newOpenFor),
            new StringList($newClosedFor),
            $newCsTrackerIssue,
        );

        if ($hasEvent) {
            $this->entityManagerMock->expects($this->once())->method('persist')
                ->willReturnCallback(function (Event $event) use ($nowOpenFor, $noLongerOpenFor, $newCsTrackerIssue): void {
                    self::assertSame('Creator w/event test', $event->getCreatorName());
                    self::assertSameItems($nowOpenFor, $event->getNowOpenForArray());
                    self::assertSameItems($noLongerOpenFor, $event->getNoLongerOpenForArray());
                    self::assertSame(Event::TYPE_CS_UPDATED, $event->getType());
                    self::assertSame($newCsTrackerIssue, $event->getTrackingIssues());
                    self::assertSameItems(['https://getfursu.it/', 'https://getfursu.it/info'], $event->getCheckedUrlsArray());
                });
        } else {
            $this->entityManagerMock->expects($this->never())->method('persist');
        }

        $this->subject->applyResults($creator, $analysisResults);
    }
}
