<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Artisan;
use App\Entity\EventFactory;
use App\Utils\Data\ArtisanChanges;
use PHPUnit\Framework\TestCase;

class EventFactoryTest extends TestCase
{
    /**
     * @dataProvider fromArtisanChangesDataProvider
     *
     * @param string[] $expectedNoLongerOpenFor
     * @param string[] $expectedNowOpenFor
     */
    public function testFromArtisanChanges(ArtisanChanges $inputArtisanChanges, bool $expectedHadTrackerIssues,
    string $expectedArtisanName, array $expectedNoLongerOpenFor, array $expectedNowOpenFor): void
    {
        $result = EventFactory::fromArtisanChanges($inputArtisanChanges);

        self::assertEquals($expectedHadTrackerIssues, $result->getTrackingIssues());
        self::assertEquals($expectedArtisanName, $result->getArtisanName());
        self::assertEquals($expectedNoLongerOpenFor, $result->getNoLongerOpenForArray());
        self::assertEquals($expectedNowOpenFor, $result->getNowOpenForArray());
    }

    public function fromArtisanChangesDataProvider(): array
    {
        $artisan1 = (new Artisan())
            ->setName('Artisan name 1')
            ->setOpenFor("Commissions\nPre-mades")
            ->setCsTrackerIssue(false)
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
        ;
        $changes3 = new ArtisanChanges($artisan3);
        $changes3->getChanged()
            ->setOpenFor('')
            ->setCsTrackerIssue(false)
        ;

        return [
            [$changes1, true, 'Artisan name 1', ['Commissions'], ['Artistic liberty']],
            [$changes2, false, 'Artisan name 2', [], ['New stuff']],
            [$changes3, false, 'Artisan name 3', ['Old stuff'], []],
        ];
    }
}
