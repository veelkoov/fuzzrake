<?php

declare(strict_types=1);

namespace App\Tests\Event\Doctrine;

use App\Data\Definitions\Ages;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator;
use Exception;

/**
 * @medium
 */
class CreatorListenerTest extends FuzzrakeKernelTestCase
{
    /**
     * @throws Exception
     */
    public function testPersistingCreatorsSetsSafeValues(): void
    {
        self::persistAndFlush(
            (new SmartAccessDecorator())
                ->setCreatorId('TEST001')
                ->setDoesNsfw(true)
                ->setNsfwWebsite(true)
                ->setAges(Ages::MIXED)
                ->setWorksWithMinors(true)
        );
        self::clear();

        $retrieved = new SmartAccessDecorator(self::getCreatorRepository()->findByCreatorId('TEST001'));

        self::assertEquals(false, $retrieved->getDoesNsfw());
        self::assertEquals(false, $retrieved->getWorksWithMinors());
    }

    /**
     * @throws Exception
     */
    public function testUpdatingCreatorsSetsSafeValues(): void
    {
        self::persistAndFlush(
            (new SmartAccessDecorator())
                ->setCreatorId('TEST001')
                ->setDoesNsfw(false)
                ->setNsfwWebsite(false)
                ->setNsfwSocial(false)
                ->setAges(Ages::ADULTS)
                ->setWorksWithMinors(true)
        );
        self::clear();

        $updated = new SmartAccessDecorator(self::getCreatorRepository()->findByCreatorId('TEST001'));

        self::assertEquals(false, $updated->getDoesNsfw());
        self::assertEquals(true, $updated->getWorksWithMinors());

        $updated
            ->setNsfwWebsite(true)
            ->setAges(Ages::MINORS)
            ->setDoesNsfw(true)
        ;

        self::flushAndClear();

        $retrieved = new SmartAccessDecorator(self::getCreatorRepository()->findByCreatorId('TEST001'));

        self::assertEquals(true, $retrieved->getNsfwWebsite());
        self::assertEquals(false, $retrieved->getDoesNsfw());
        self::assertEquals(false, $retrieved->getWorksWithMinors());
    }
}
