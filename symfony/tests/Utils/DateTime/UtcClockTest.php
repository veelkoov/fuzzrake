<?php

declare(strict_types=1);

namespace App\Tests\Utils\DateTime;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\TestsBridge;
use App\Utils\TestUtils\UtcClockMock;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class UtcClockTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }

    public function testGetUtc(): void
    {
        $subject = UtcClock::getUtc();

        self::assertEquals('UTC', $subject->getName());
    }

    public function testNow(): void
    {
        $subject = UtcClock::now();

        self::assertEqualsWithDelta(time(), $subject->getTimestamp(), 1.1);
        self::assertEquals('UTC', $subject->getTimezone()->getName());
    }

    /**
     * @throws DateTimeException
     */
    public function testAt(): void
    {
        $subject = UtcClock::at('2022-01-07 13:01');

        self::assertEquals('2022-01-07T13:01:00.000+00:00', $subject->format(DATE_RFC3339_EXTENDED));
        self::assertEquals('UTC', $subject->getTimezone()->getName());

        try {
            UtcClock::at(false);
            self::fail();
        } catch (DateTimeException) {
            // Expected
        }

        try {
            UtcClock::at(null);
            self::fail();
        } catch (DateTimeException) {
            // Expected
        }

        try {
            UtcClock::at('some invalid info');
            self::fail();
        } catch (DateTimeException) {
            // Expected
        }
    }

    public function testFromTimestamp(): void
    {
        $subject = UtcClock::fromTimestamp(1658658993);

        self::assertEquals('2022-07-24T10:36:33.000+00:00', $subject->format(DATE_RFC3339_EXTENDED));
        self::assertEquals('UTC', $subject->getTimezone()->getName());
    }

    /**
     * @throws DateTimeException
     */
    public function testGetMonthLaterYmd(): void
    {
        UtcClockMock::start();

        $daysInThisMonth = (int) UtcClock::now()->format('t');
        self::forTestGetXyzLaterYmd(UtcClock::getMonthLaterYmd(), $daysInThisMonth);
    }

    /**
     * @throws DateTimeException
     */
    public function testGetWeekLaterYmd(): void
    {
        UtcClockMock::start();

        self::forTestGetXyzLaterYmd(UtcClock::getWeekLaterYmd(), 7);
    }

    /**
     * @throws DateTimeException
     */
    public function testTomorrowYmd(): void
    {
        UtcClockMock::start();

        self::forTestGetXyzLaterYmd(UtcClock::getTomorrowYmd(), 1);
    }

    /**
     * @throws DateTimeException
     */
    public function forTestGetXyzLaterYmd(string $actualYmd, int $daysCount): void
    {
        $todayYmd = UtcClock::now()->format('Y-m-d');

        self::assertTrue($actualYmd > $todayYmd, "False: $actualYmd > $todayYmd");

        $timestampMidnight = UtcClock::at($todayYmd)->getTimestamp();
        $timestampNow = UtcClock::now()->getTimestamp();

        $secondsSinceMidnight = $timestampNow - $timestampMidnight;

        // Pass time to one second before the midnight, $daysCount days in advance
        UtcClockMock::passMs(1000 * (24 * 60 * 60 * $daysCount - $secondsSinceMidnight - 1));

        $todayYmd = UtcClock::now()->format('Y-m-d');
        self::assertTrue($actualYmd > $todayYmd, "False: $actualYmd > $todayYmd");

        // Pass time to one second after the midnight
        UtcClockMock::passMs(1000 * 2);

        $todayYmd = UtcClock::now()->format('Y-m-d');
        self::assertTrue($todayYmd >= $actualYmd, "False: $actualYmd > $todayYmd");
    }

    /**
     * @throws DateTimeException
     */
    public function testPassed(): void
    {
        $subject = UtcClock::at('+1 second');
        self::assertFalse(UtcClock::passed($subject));

        $subject = UtcClock::at('-1 second');
        self::assertTrue(UtcClock::passed($subject));

        UtcClockMock::start();

        $subject = UtcClock::at('+1 second');
        self::assertFalse(UtcClock::passed($subject));

        UtcClockMock::passMs(2000);
        self::assertTrue(UtcClock::passed($subject));
    }

    public function testTimems(): void
    {
        $expected = UtcClock::timems();

        self::assertEqualsWithDelta($expected, microtime(true) * 1000, 100);

        UtcClockMock::start();
        $millisecondsToPass = 5;

        $expected = UtcClock::timems() + $millisecondsToPass;
        UtcClockMock::passMs($millisecondsToPass);

        self::assertEquals($expected, UtcClock::timems());
    }

    public function testTime(): void
    {
        $expected = UtcClock::time();

        self::assertEqualsWithDelta($expected, time(), 1.1);

        UtcClockMock::start();
        $secondsToPass = 5;

        $expected = UtcClock::time() + $secondsToPass;
        UtcClockMock::passMs(1000 * $secondsToPass);

        self::assertEquals($expected, UtcClock::time());
    }
}
