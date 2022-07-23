<?php

declare(strict_types=1);

namespace App\Utils\TestUtils;

use App\Utils\Traits\UtilityClass;

/**
 * ClockMock doesn't work with kernel-enabled tests.
 */
final class UtcClockMock
{
    use UtilityClass;

    public static function timems(): int
    {
        return TestsBridge::getTimeMs() ?? self::actualTimems();
    }

    public static function time(): int
    {
        return (int) (self::timems() / 1000);
    }

    public static function start(): void
    {
        TestsBridge::setTimeMs(self::actualTimems());
    }

    public static function finish(): void
    {
        TestsBridge::resetTimeMs();
    }

    public static function passMs(int $msToPass): void
    {
        TestsBridge::setTimeMs(self::timems() + $msToPass);
    }

    private static function actualTimems(): int
    {
        return (int) (microtime(true) * 1000);
    }
}
