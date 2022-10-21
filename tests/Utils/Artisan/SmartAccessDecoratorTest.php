<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SmartAccessDecoratorTest extends TestCase
{
    /**
     * @throws DateTimeException
     */
    public function testEquals(): void
    {
        $subject1 = Artisan::new()
            ->setIntro('Same intro')
            ->setCity('Oulu')
            ->setDateAdded(UtcClock::at('2022-09-23 11:46:11'))
            ->setDateUpdated(UtcClock::at('2022-09-24 12:34:56'))
            ->setOtherFeatures("abcd\nefgh\nijkl")
            ->setOtherStyles("qwer\nasdf\nzxcv")
        ;
        $subject2 = Artisan::new()
            ->setIntro('Same intro')
            ->setCity('Kuopio')
            ->setDateAdded(UtcClock::at('2022-09-23 11:46:11'))
            ->setDateUpdated(UtcClock::at('2022-09-24 11:22:33'))
            ->setOtherFeatures("abcd\nijkl\nefgh")
            ->setOtherStyles("qwer\nasdf")
        ;

        self::assertTrue($subject1->equals(Field::INTRO, $subject2));
        self::assertFalse($subject1->equals(Field::CITY, $subject2));
        self::assertTrue($subject1->equals(Field::DATE_ADDED, $subject2));
        self::assertFalse($subject1->equals(Field::DATE_UPDATED, $subject2));
        self::assertTrue($subject1->equals(Field::OTHER_FEATURES, $subject2));
        self::assertFalse($subject1->equals(Field::OTHER_STYLES, $subject2));
    }
}
