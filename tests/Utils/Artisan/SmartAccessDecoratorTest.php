<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Entity\ArtisanValue;
use App\Tests\TestUtils\DbEnabledKernelTestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class SmartAccessDecoratorTest extends DbEnabledKernelTestCase
{
    public function testBooleanValues(): void
    {
        self::bootKernel();
        $repo = self::getEM()->getRepository(ArtisanValue::class);

        $artisan = new Artisan();
        $artisan->setIsMinor(null);
        $artisan->setWorksWithMinors(null);
        self::persistAndFlush($artisan);

        $all = $repo->findAll();
        self::assertCount(0, $all);

        $artisan->setIsMinor(true);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(1, $all);
        self::assertEquals('IS_MINOR', $all[0]->getFieldName());
        self::assertEquals('True', $all[0]->getValue());
        $first = $all[0];

        $artisan->setWorksWithMinors(false);
        $artisan->setIsMinor(null);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(1, $all);
        self::assertEquals('WORKS_WITH_MINORS', $all[0]->getFieldName());
        self::assertEquals('False', $all[0]->getValue());
        self::assertNotEquals($first->getId(), $all[0]->getId());
        self::assertNotSame($first, $all[0]);

        $artisan->setWorksWithMinors(null);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(0, $all);
    }
}
