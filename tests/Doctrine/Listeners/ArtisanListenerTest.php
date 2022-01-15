<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Listeners;

use App\DataDefinitions\Ages;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator;
use Exception;

class ArtisanListenerTest extends KernelTestCaseWithEM
{
    /**
     * @throws Exception
     */
    public function testPersistingArtisanSetsSafeValues(): void
    {
        self::bootKernel();

        self::persistAndFlush(
            (new SmartAccessDecorator())
                ->setMakerId('SAFEMID')
                ->setDoesNsfw(true)
                ->setNsfwWebsite(true)
                ->setAges(Ages::MIXED)
                ->setWorksWithMinors(true)
        );
        self::clear();

        $retrieved = new SmartAccessDecorator(self::getArtisanRepository()->findByMakerId('SAFEMID'));

        self::assertEquals(false, $retrieved->getDoesNsfw());
        self::assertEquals(false, $retrieved->getWorksWithMinors());
    }

    /**
     * @throws Exception
     */
    public function testUpdatingArtisanSetsSafeValues(): void
    {
        self::bootKernel();

        self::persistAndFlush(
            (new SmartAccessDecorator())
                ->setMakerId('SAFEMID')
                ->setDoesNsfw(false)
                ->setNsfwWebsite(false)
                ->setNsfwSocial(false)
                ->setAges(Ages::ADULTS)
                ->setWorksWithMinors(true)
        );
        self::clear();

        $updated = new SmartAccessDecorator(self::getArtisanRepository()->findByMakerId('SAFEMID'));

        self::assertEquals(false, $updated->getDoesNsfw());
        self::assertEquals(true, $updated->getWorksWithMinors());

        $updated
            ->setNsfwWebsite(true)
            ->setAges(Ages::MINORS)
            ->setDoesNsfw(true)
        ;

        self::flushAndClear();

        $retrieved = new SmartAccessDecorator(self::getArtisanRepository()->findByMakerId('SAFEMID'));

        self::assertEquals(true, $retrieved->getNsfwWebsite());
        self::assertEquals(false, $retrieved->getDoesNsfw());
        self::assertEquals(false, $retrieved->getWorksWithMinors());
    }
}
