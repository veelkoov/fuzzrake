<?php

declare(strict_types=1);

namespace App\Utils\TestUtils;

use App\Utils\Traits\UtilityClass;
use Psl\File;
use Psl\Filesystem;

final class TestsBridge
{
    use UtilityClass;

    private const string MOCKS_DIRECTORY_PATH = __DIR__.'/../../../var/cache/test';
    private const string MOCK_TIMESTAMP_PATH = self::MOCKS_DIRECTORY_PATH.'/timestamp.txt';

    public static function isTest(): bool
    {
        return 'test' === ($_ENV['APP_ENV'] ?? null);
    }

    public static function reset(): void
    {
        self::removeBridgeFile(self::MOCK_TIMESTAMP_PATH);
    }

    // ===== TIME MOCKING =====

    public static function setTimeMs(int $timeMsToSet): void
    {
        self::writeBridgeFileInt(self::MOCK_TIMESTAMP_PATH, $timeMsToSet);
    }

    public static function getTimeMs(): ?int
    {
        return self::readBridgeFileInt(self::MOCK_TIMESTAMP_PATH);
    }

    // ===== UTILITIES =====

    /**
     * @param non-empty-string $filepath
     */
    private static function writeBridgeFileInt(string $filepath, int $number): void
    {
        self::assureDirectoryExists();

        File\write($filepath, (string) $number);
    }

    /**
     * @param non-empty-string $filepath
     */
    private static function removeBridgeFile(string $filepath): void
    {
        if (file_exists($filepath)) {
            Filesystem\delete_file($filepath);
        }
    }

    /**
     * @param non-empty-string $filepath
     */
    private static function readBridgeFileInt(string $filepath): ?int
    {
        if (!file_exists($filepath)) {
            return null;
        }

        return (int) File\read($filepath);
    }

    private static function assureDirectoryExists(): void
    {
        if (!Filesystem\is_directory(self::MOCKS_DIRECTORY_PATH)) {
            Filesystem\create_directory(self::MOCKS_DIRECTORY_PATH);
        }
    }
}
