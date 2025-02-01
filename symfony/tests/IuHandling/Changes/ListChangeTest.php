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
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer'), StringList::of('qwer'));

        self::assertEquals('OTHER_FEATURES did not change', $subject->getDescription());
        self::assertFalse($subject->isActuallyAChange());
    }

    public function testAdded(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of(), StringList::of('qwer'));

        self::assertEquals('Added OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer'), StringList::of('qwer', 'asdf'));

        self::assertEquals('Added OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer', 'asdf'), StringList::of('qwer'));

        self::assertEquals('Removed OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer'), StringList::of());

        self::assertEquals('Removed OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testAddedAndRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('asdf', 'qwer'), StringList::of('qwer', 'zxcv'));

        self::assertEquals('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('asdf'), StringList::of('zxcv'));

        self::assertEquals('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('asdf', 'qwer'), StringList::of('zxcv', 'uiop'));

        self::assertEquals('Added OTHER_FEATURES: "zxcv", "uiop" and removed: "asdf", "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }
}
