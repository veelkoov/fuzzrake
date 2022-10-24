<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\DataDefinitions\Fields\Field;
use App\Entity\EventFactory;
use App\IuHandling\Changes\ChangeInterface;
use App\IuHandling\Changes\ListChange;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class EventFactoryTest extends TestCase
{
    /**
     * @dataProvider fromArtisanChangesDataProvider
     *
     * @param string[] $expectedNoLongerOpenFor
     * @param string[] $expectedNowOpenFor
     * @param string[] $expectedCheckedUrls
     */
    public function testForCsTracker(ChangeInterface $inputArtisanChanges, Artisan $updatedArtisan, bool $expectedHadTrackerIssues,
                                     string $expectedArtisanName, array $expectedNoLongerOpenFor, array $expectedNowOpenFor, array $expectedCheckedUrls): void
    {
        $result = EventFactory::forStatusTracker($inputArtisanChanges, $updatedArtisan);

        self::assertEquals('CS_UPDATED', $result->getType());
        self::assertEquals($expectedHadTrackerIssues, $result->getTrackingIssues());
        self::assertEquals($expectedArtisanName, $result->getArtisanName());
        self::assertEquals($expectedNoLongerOpenFor, $result->getNoLongerOpenForArray());
        self::assertEquals($expectedNowOpenFor, $result->getNowOpenForArray());
        self::assertEquals($expectedCheckedUrls, $result->getCheckedUrlsArray());
    }

    /**
     * @return array<array{ChangeInterface, Artisan, bool, string, string[], string[], string[]}>
     */
    public function fromArtisanChangesDataProvider(): array
    {
        $artisan1 = Artisan::new()
            ->setName('Artisan name 1')
            ->setOpenFor("Commissions\nPre-mades")
            ->setCsTrackerIssue(false)
            ->setCommissionsUrls("abc1\ndef1")
        ;
        $changes1 = new ListChange(Field::OPEN_FOR, $artisan1->getOpenFor(), "Pre-mades\nArtistic liberty");

        $artisan2 = Artisan::new()
            ->setName('Artisan name 2')
            ->setOpenFor('')
            ->setCsTrackerIssue(true)
            ->setCommissionsUrls('def2')
        ;
        $changes2 = new ListChange(Field::OPEN_FOR, $artisan2->getOpenFor(), 'New stuff');

        $artisan3 = Artisan::new()
            ->setName('Artisan name 3')
            ->setOpenFor('Old stuff')
            ->setCsTrackerIssue(false)
            ->setCommissionsUrls("abc3\ndef3\nghi3")
        ;
        $changes3 = new ListChange(Field::OPEN_FOR, $artisan3->getOpenFor(), '');

        return [
            [$changes1, $artisan1, false, 'Artisan name 1', ['Commissions'], ['Artistic liberty'], ['abc1', 'def1']],
            [$changes2, $artisan2, true, 'Artisan name 2', [], ['New stuff'], ['def2']],
            [$changes3, $artisan3, false, 'Artisan name 3', ['Old stuff'], [], ['abc3', 'def3', 'ghi3']],
        ];
    }
}
