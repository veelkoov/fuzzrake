<?php

declare(strict_types=1);

namespace App\Utils\TestUtils;

use App\Utils\Traits\UtilityClass;
use LogicException;

use function Psl\File\read;
use function Psl\File\write;
use function Psl\Filesystem\create_directory;
use function Psl\Filesystem\delete_file;
use function Psl\Filesystem\is_directory;

final class TestsBridge
{
    use UtilityClass;

    private const MOCKS_DIRECTORY_PATH = __DIR__.'/../../../var/cache/test';
    private const MOCK_TIMESTAMP_PATH = self::MOCKS_DIRECTORY_PATH.'/timestamp.txt';
    private const MARK_SKIP_SINGLE_CAPTCHA_PATH = self::MOCKS_DIRECTORY_PATH.'/skip-single-captcha.txt';
    private const CAPTCHA_SKIP_TIMEOUT_SECONDS = 30;

    public static function isTest(): bool
    {
        return 'test' === ($_ENV['APP_ENV'] ?? null);
    }

    // ===== CAPTCHA MOCKING =====

    public static function setSkipSingleCaptcha(): void
    {
        self::writeBridgeFileInt(self::MARK_SKIP_SINGLE_CAPTCHA_PATH, time());
    }

    public static function shouldSkipSingleCaptcha(): bool
    {
        if (!self::isTest()) {
            throw new LogicException('This must not be called outside tests');
        }

        $timestamp = self::readBridgeFileInt(self::MARK_SKIP_SINGLE_CAPTCHA_PATH);

        if (null !== $timestamp && time() <= $timestamp + self::CAPTCHA_SKIP_TIMEOUT_SECONDS) {
            self::resetCaptcha();

            return true;
        }

        return false;
    }

    public static function resetCaptcha(): void
    {
        self::removeBridgeFile(self::MARK_SKIP_SINGLE_CAPTCHA_PATH);
    }

    // ===== TIME MOCKING =====

    public static function setTimeMs(int $timeMsToSet): void
    {
        self::writeBridgeFileInt(self::MOCK_TIMESTAMP_PATH, $timeMsToSet);
    }

    public static function resetTimeMs(): void
    {
        self::removeBridgeFile(self::MOCK_TIMESTAMP_PATH);
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

        write($filepath, (string) $number);
    }

    /**
     * @param non-empty-string $filepath
     */
    private static function removeBridgeFile(string $filepath): void
    {
        if (file_exists($filepath)) {
            delete_file($filepath);
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

        return (int) read($filepath);
    }

    private static function assureDirectoryExists(): void
    {
        if (!is_directory(self::MOCKS_DIRECTORY_PATH)) {
            create_directory(self::MOCKS_DIRECTORY_PATH);
        }
    }
}
