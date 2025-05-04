<?php

declare(strict_types=1);

namespace App\Tests\Utils\DateTime;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class DateTimeUtilsTest extends TestCase
{
    /**
     * @throws DateTimeException
     */
    public function testEqual(): void
    {
        self::assertTrue(DateTimeUtils::equal(null, null));
        self::assertFalse(DateTimeUtils::equal(null, UtcClock::now()));
        self::assertFalse(DateTimeUtils::equal(UtcClock::now(), null));
        self::assertTrue(DateTimeUtils::equal(UtcClock::at('2022-09-18 21:19:50'), UtcClock::at('2022-09-18 21:19:50')));
        self::assertFalse(DateTimeUtils::equal(UtcClock::at('2022-09-18 21:19:50'), UtcClock::at('2022-09-18 21:19:51')));
    }
}
