<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Changes;

use App\DataDefinitions\Fields\Field;
use App\IuHandling\Changes\ListChange;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class ListChangeTest extends TestCase
{
    public function testNoChange(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, 'qwer', 'qwer');

        self::assertEquals('OTHER_FEATURES did not change', $subject->getDescription());
        self::assertFalse($subject->isActuallyAChange());
    }

    public function testAdded(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, '', 'qwer');

        self::assertEquals('Added OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, 'qwer', "qwer\nasdf");

        self::assertEquals('Added OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, "qwer\nasdf", 'qwer');

        self::assertEquals('Removed OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, 'qwer', '');

        self::assertEquals('Removed OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testAddedAndRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, "asdf\nqwer", "qwer\nzxcv");

        self::assertEquals('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, 'asdf', 'zxcv');

        self::assertEquals('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, "asdf\nqwer", "zxcv\nuiop");

        self::assertEquals('Added OTHER_FEATURES: "zxcv", "uiop" and removed: "asdf", "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }
}
