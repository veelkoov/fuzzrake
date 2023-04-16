<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Changes;

use App\Data\Definitions\Fields\Field;
use App\IuHandling\Changes\SimpleChange;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SimpleChangeTest extends TestCase
{
    public function testNoChange(): void
    {
        $subject = new SimpleChange(Field::MAKER_ID, 'asdfqwerzxcv', 'asdfqwerzxcv');

        self::assertEquals('MAKER_ID did not change', $subject->getDescription());
        self::assertFalse($subject->isActuallyAChange());
    }

    public function testAdded(): void
    {
        $subject = new SimpleChange(Field::MAKER_ID, null, 'asdfqwerzxcv');

        self::assertEquals('Added MAKER_ID: "asdfqwerzxcv"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new SimpleChange(Field::MAKER_ID, '', 'asdfqwerzxcv');

        self::assertEquals('Added MAKER_ID: "asdfqwerzxcv"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testRemoved(): void
    {
        $subject = new SimpleChange(Field::MAKER_ID, 'asdfqwerzxcv', null);

        self::assertEquals('Removed MAKER_ID: "asdfqwerzxcv"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new SimpleChange(Field::MAKER_ID, 'asdfqwerzxcv', '');

        self::assertEquals('Removed MAKER_ID: "asdfqwerzxcv"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }

    public function testChanged(): void
    {
        $subject = new SimpleChange(Field::MAKER_ID, '', null);

        self::assertEquals('Changed MAKER_ID from "" to unknown', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new SimpleChange(Field::MAKER_ID, null, '');

        self::assertEquals('Changed MAKER_ID from unknown to ""', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());

        $subject = new SimpleChange(Field::MAKER_ID, 'asdf', 'qwer');

        self::assertEquals('Changed MAKER_ID from "asdf" to "qwer"', $subject->getDescription());
        self::assertTrue($subject->isActuallyAChange());
    }
}
