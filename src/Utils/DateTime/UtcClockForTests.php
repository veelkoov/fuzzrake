<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;

final class UtcClockForTests
{
    use UtilityClass;

    public static function timems(): int
    {
        $path = self::getTimestampPath();

        if (!file_exists($path)) {
            file_put_contents($path, (string) (int) (microtime(true) * 1000));
        }

        return (int) file_get_contents($path);
    }

    public static function time(): int
    {
        return (int) (self::timems() / 1000);
    }

    public static function reset(): void
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
}
