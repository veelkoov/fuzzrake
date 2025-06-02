<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Data\Definitions\Ages;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator;
use Exception;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class CreatorMediumTest extends FuzzrakeKernelTestCase
{
    /**
     * @throws Exception
     */
    public function testPersistingCreatorEnforcesSafeValues(): void
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

        self::assertFalse($retrieved->getDoesNsfw());
        self::assertFalse($retrieved->getWorksWithMinors());
    }

    /**
     * @throws Exception
     */
    public function testUpdatingCreatorEnforcesSafeValues(): void
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

        self::assertFalse($updated->getDoesNsfw());
        self::assertTrue($updated->getWorksWithMinors());

        $updated
            ->setNsfwWebsite(true)
            ->setAges(Ages::MINORS)
            ->setDoesNsfw(true)
        ;

        self::flushAndClear();

        $retrieved = new SmartAccessDecorator(self::getCreatorRepository()->findByCreatorId('TEST001'));

        self::assertTrue($retrieved->getNsfwWebsite());
        self::assertFalse($retrieved->getDoesNsfw());
        self::assertFalse($retrieved->getWorksWithMinors());
    }
}
