<?php

declare(strict_types=1);

namespace App\Tests\Utils\Creator\Changes;

use App\Data\Definitions\Fields\Field;
use App\Utils\Creator\Changes\ListChange;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\Lists\StringList;

#[Small]
class ListChangeTest extends TestCase
{
    public function testNoChange(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer'), StringList::of('qwer'));

        self::assertSame('OTHER_FEATURES did not change', $subject->getDescription());
        self::assertFalse($subject->isActuallyAChange());
    }

    public function testAdded(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of(), StringList::of('qwer'));

        self::assertSame('Added OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer'), StringList::of('qwer', 'asdf'));

        self::assertSame('Added OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer', 'asdf'), StringList::of('qwer'));

        self::assertSame('Removed OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('qwer'), StringList::of());

        self::assertSame('Removed OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testAddedAndRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('asdf', 'qwer'), StringList::of('qwer', 'zxcv'));

        self::assertSame('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('asdf'), StringList::of('zxcv'));

        self::assertSame('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringList::of('asdf', 'qwer'), StringList::of('zxcv', 'uiop'));

        self::assertSame('Added OTHER_FEATURES: "zxcv", "uiop" and removed: "asdf", "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }
}
