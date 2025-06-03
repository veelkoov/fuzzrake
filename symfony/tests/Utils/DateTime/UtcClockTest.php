<?php

declare(strict_types=1);

namespace App\Tests\Utils\DateTime;

use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;

#[Small]
class UtcClockTest extends FuzzrakeTestCase
{
    use ClockSensitiveTrait;

    public function testGetUtc(): void
    {
        $subject = UtcClock::getUtc();

        self::assertSame('UTC', $subject->getName());
    }

    public function testNow(): void
    {
        $subject = UtcClock::now();

        self::assertEqualsWithDelta(time(), $subject->getTimestamp(), 1.1);
        self::assertSame('UTC', $subject->getTimezone()->getName());
    }

    /**
     * @throws DateTimeException
     */
    public function testAt(): void
    {
        $subject = UtcClock::at('2022-01-07 13:01');

        self::assertEquals('2022-01-07T13:01:00.000+00:00', $subject->format(DATE_RFC3339_EXTENDED));
        self::assertSame('UTC', $subject->getTimezone()->getName());
    }

    #[DataProvider('atThrowsOnInvalidDataProvider')]
    public function testAtThrowsOnInvalid(string|int|bool|null $input): void
    {
        $this->expectNotToPerformAssertions();

        try {
            UtcClock::at((string) $input);
            self::fail();
        } catch (DateTimeException) {
            // Expected
        }
    }

    public static function atThrowsOnInvalidDataProvider(): TestDataProvider
    {
        // The method will be used in some cases where data will be typehinted as many different things.
        // Example: Doctrine's single scalar result. The simplest solution is to (string) cast.
        // Below cases cover also the least possible.
        return TestDataProvider::list('some invalid info', '', '0', '1', 0, 1, false, true, null);
    }

    public function testFromTimestamp(): void
    {
        $subject = UtcClock::fromTimestamp(1658658993);

        self::assertEquals('2022-07-24T10:36:33.000+00:00', $subject->format(DATE_RFC3339_EXTENDED));
        self::assertSame('UTC', $subject->getTimezone()->getName());
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

        self::mockTime();

        $subject = UtcClock::at('+1 second');
        self::assertFalse(UtcClock::passed($subject));

        UtcClock::get()->sleep(2);

        self::assertTrue(UtcClock::passed($subject));
    }

    public function testTime(): void
    {
        $expected = UtcClock::time();

        self::assertEqualsWithDelta($expected, time(), 1.1);

        self::mockTime();
        $secondsToPass = 5;

        $expected = UtcClock::time() + $secondsToPass;
        UtcClock::get()->sleep($secondsToPass);

        self::assertSame($expected, UtcClock::time());
    }
}
