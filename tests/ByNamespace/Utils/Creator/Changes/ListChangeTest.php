<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Utils\Creator\Changes;

use App\Data\Definitions\Fields\Field;
use App\Utils\Creator\Changes\ListChange;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\Vecs\StringVec;

#[Small]
class ListChangeTest extends TestCase
{
    public function testNoChange(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of('qwer'), StringVec::of('qwer'));

        self::assertSame('OTHER_FEATURES did not change', $subject->getDescription());
        self::assertFalse($subject->isActuallyAChange());
    }

    public function testAdded(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of(), StringVec::of('qwer'));

        self::assertSame('Added OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of('qwer'), StringVec::of('qwer', 'asdf'));

        self::assertSame('Added OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of('qwer', 'asdf'), StringVec::of('qwer'));

        self::assertSame('Removed OTHER_FEATURES: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of('qwer'), StringVec::of());

        self::assertSame('Removed OTHER_FEATURES: "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testAddedAndRemoved(): void
    {
        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of('asdf', 'qwer'), StringVec::of('qwer', 'zxcv'));

        self::assertSame('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of('asdf'), StringVec::of('zxcv'));

        self::assertSame('Added OTHER_FEATURES: "zxcv" and removed: "asdf"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new ListChange(Field::OTHER_FEATURES, StringVec::of('asdf', 'qwer'), StringVec::of('zxcv', 'uiop'));

        self::assertSame('Added OTHER_FEATURES: "zxcv", "uiop" and removed: "asdf", "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }
}
