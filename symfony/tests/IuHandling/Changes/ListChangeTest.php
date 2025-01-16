<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;
use App\IuHandling\Changes\ListChange;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\StringList;

/**
 * @small
 */
class ListChangeTest extends TestCase
{
    public function testNoChange(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, new StringList('qwer'), new StringList('qwer'));

        self::assertEquals('OTHER_FEATURES did not change', $subject->getDescription());
        self::assertFalse($subject->isActuallyAChange());
    }

    public function testAdded(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, new StringList(), new StringList('qwer'));

        self::assertEquals('Added OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, new StringList('qwer'), new StringList('qwer', 'asdf'));

        self::assertEquals('Added OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, new StringList('qwer', 'asdf'), new StringList('qwer'));

        self::assertEquals('Removed OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, new StringList('qwer'), new StringList());

        self::assertEquals('Removed OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testAddedAndRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, new StringList('asdf', 'qwer'), new StringList('qwer', 'zxcv'));

        self::assertEquals('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, new StringList('asdf'), new StringList('zxcv'));

        self::assertEquals('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, new StringList('asdf', 'qwer'), new StringList('zxcv', 'uiop'));

        self::assertEquals('Added OTHER_FEATURES: "zxcv", "uiop" and removed: "asdf", "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }
}
