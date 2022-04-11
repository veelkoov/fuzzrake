<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;

/**
 * ClockMock doesn't work with kernel-enabled tests.
 */
final class UtcClockForTests
{
    use UtilityClass;

    public static function timems(): int
    {
        $path = self::getTimestampPath();

        return !file_exists($path) ? self::actualTimems() : (int) file_get_contents($path);
    }

    public static function time(): int
    {
        return (int) (self::timems() / 1000);
    }

    public static function start(): void
    {
        file_put_contents(self::getTimestampPath(), (string) self::actualTimems());
    }

    public static function finish(): void
    {
        unlink(self::getTimestampPath());
    }

    private static function rootDirPath(): string
    {
        return realpath(__DIR__.'/'.str_repeat('../', substr_count(self::class, '\\')));
    }

    private static function getTimestampPath(): string
    {
        return self::rootDirPath().'/var/cache/test/timestamp.txt';
    }

    private static function actualTimems(): int
    {
        return (int) (microtime(true) * 1000);
    }
}
