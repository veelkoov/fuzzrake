<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\EventFactory;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\ArtisanChanges;
use PHPUnit\Framework\TestCase;

class EventFactoryTest extends TestCase
{
    /**
     * @dataProvider fromArtisanChangesDataProvider
     *
     * @param string[] $expectedNoLongerOpenFor
     * @param string[] $expectedNowOpenFor
     * @param string[] $expectedCheckedUrls
     */
    public function testForCsTracker(ArtisanChanges $inputArtisanChanges, bool $expectedHadTrackerIssues,
    string $expectedArtisanName, array $expectedNoLongerOpenFor, array $expectedNowOpenFor, array $expectedCheckedUrls): void
    {
        $result = EventFactory::forCsTracker($inputArtisanChanges);

        self::assertEquals('CS_UPDATED', $result->getType());
        self::assertEquals($expectedHadTrackerIssues, $result->getTrackingIssues());
        self::assertEquals($expectedArtisanName, $result->getArtisanName());
        self::assertEquals($expectedNoLongerOpenFor, $result->getNoLongerOpenForArray());
        self::assertEquals($expectedNowOpenFor, $result->getNowOpenForArray());
        self::assertEquals($expectedCheckedUrls, $result->getCheckedUrlsArray());
    }

    public function fromArtisanChangesDataProvider(): array
    {
        $artisan1 = (new Artisan())
            ->setName('Artisan name 1')
            ->setOpenFor("Commissions\nPre-mades")
            ->setCsTrackerIssue(false)
            ->setCommissionsUrls("abc1\ndef1")
        ;
        $changes1 = new ArtisanChanges($artisan1);
        $changes1->getChanged()
            ->setOpenFor("Pre-mades\nArtistic liberty")
            ->setCsTrackerIssue(true)
        ;

        $artisan2 = (new Artisan())
            ->setName('Artisan name 2')
            ->setOpenFor('')
            ->setCsTrackerIssue(true)
            ->setCommissionsUrls('def2')
        ;
        $changes2 = new ArtisanChanges($artisan2);
        $changes2->getChanged()
            ->setOpenFor('New stuff')
            ->setCsTrackerIssue(false)
        ;

        $artisan3 = (new Artisan())
            ->setName('Artisan name 3')
            ->setOpenFor('Old stuff')
            ->setCsTrackerIssue(false)
            ->setCommissionsUrls("abc3\ndef3\nghi3")
        ;
        $changes3 = new ArtisanChanges($artisan3);
        $changes3->getChanged()
            ->setOpenFor('')
            ->setCsTrackerIssue(false)
        ;

        return [
            [$changes1, true, 'Artisan name 1', ['Commissions'], ['Artistic liberty'], ['abc1', 'def1']],
            [$changes2, false, 'Artisan name 2', [], ['New stuff'], ['def2']],
            [$changes3, false, 'Artisan name 3', ['Old stuff'], [], ['abc3', 'def3', 'ghi3']],
        ];
    }
}
