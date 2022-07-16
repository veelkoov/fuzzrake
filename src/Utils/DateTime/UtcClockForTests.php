<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Tests\TestUtils\Paths;
use App\Utils\Traits\UtilityClass;

use function Psl\File\read;
use function Psl\File\write;
use function Psl\Filesystem\create_directory;
use function Psl\Filesystem\get_directory;
use function Psl\Filesystem\is_directory;

/**
 * ClockMock doesn't work with kernel-enabled tests.
 */
final class UtcClockForTests
{
    use UtilityClass;

    public static function timems(): int
    {
        $path = Paths::getTimestampPath();

        return !file_exists($path) ? self::actualTimems() : (int) read($path);
    }

    public static function time(): int
    {
        return (int) (self::timems() / 1000);
    }

    public static function start(): void
    {
        self::setTimeMs(self::actualTimems());
    }

    public static function finish(): void
    {
        unlink(Paths::getTimestampPath());
    }

    public static function passMs(int $msToPass): void
    {
        self::setTimeMs(self::timems() + $msToPass);
    }

    private static function actualTimems(): int
    {
        return (int) (microtime(true) * 1000);
    }

    private static function setTimeMs(int $timeMsToSet): void
    {
        if (!is_directory(get_directory(Paths::getTimestampPath()))) {
            create_directory(get_directory(Paths::getTimestampPath()));
        }

        write(Paths::getTimestampPath(), (string) $timeMsToSet);
    }
}
