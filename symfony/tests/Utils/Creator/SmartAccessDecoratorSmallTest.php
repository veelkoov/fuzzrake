<?php

declare(strict_types=1);

namespace App\Tests\Utils\Creator;

use App\Data\Definitions\Fields\Field;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class SmartAccessDecoratorSmallTest extends TestCase
{
    /**
     * @throws DateTimeException
     */
    public function testEquals(): void
    {
        $subject1 = new Creator()
            ->setIntro('Same intro')
            ->setCity('Oulu')
            ->setDateAdded(UtcClock::at('2022-09-23 11:46:11'))
            ->setDateUpdated(UtcClock::at('2022-09-24 12:34:56'))
            ->setOtherFeatures(['abcd', 'efgh', 'ijkl'])
            ->setOtherStyles(['qwer', 'asdf', 'zxcv'])
        ;

        $subject2 = new Creator()
            ->setIntro('Same intro')
            ->setCity('Kuopio')
            ->setDateAdded(UtcClock::at('2022-09-23 11:46:11'))
            ->setDateUpdated(UtcClock::at('2022-09-24 11:22:33'))
            ->setOtherFeatures(['abcd', 'ijkl', 'efgh'])
            ->setOtherStyles(['qwer', 'asdf'])
        ;

        self::assertTrue($subject1->equals(Field::INTRO, $subject2));
        self::assertFalse($subject1->equals(Field::CITY, $subject2));
        self::assertTrue($subject1->equals(Field::DATE_ADDED, $subject2));
        self::assertFalse($subject1->equals(Field::DATE_UPDATED, $subject2));
        self::assertTrue($subject1->equals(Field::OTHER_FEATURES, $subject2));
        self::assertFalse($subject1->equals(Field::OTHER_STYLES, $subject2));
    }
}
