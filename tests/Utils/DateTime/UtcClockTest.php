<?php

declare(strict_types=1);

namespace App\Tests\Utils\DateTime;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\TestCase;

class UtcClockTest extends TestCase
{
    public function testNowUsesUtcTimeZoneType3(): void
    {
        $subject = UtcClock::now()->getTimezone();

        self::assertEquals('UTC', $subject->getName());
    }

    /**
     * @throws DateTimeException
     */
    public function testAtUsesUtcTimeZoneType3(): void
    {
        $subject = UtcClock::at('2022-01-07 13:01')->getTimezone();

        self::assertEquals('UTC', $subject->getName());
    }

    /**
     * @throws DateTimeException
     */
    public function testAtParsingCorrectly(): void
    {
        $subject = UtcClock::at('2005-05-05 05:55:55');

        self::assertEquals('2005-05-05 05:55:55 UTC', $subject->format('Y-m-d H:i:s T'));
    }
}
