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

    private const string MOCKS_DIRECTORY_PATH = __DIR__.'/../../../var/cache/test';
    private const string MOCK_TIMESTAMP_PATH = self::MOCKS_DIRECTORY_PATH.'/timestamp.txt';
    private const string MARK_SKIP_SINGLE_CAPTCHA_PATH = self::MOCKS_DIRECTORY_PATH.'/skip-single-captcha.txt';
    private const int CAPTCHA_SKIP_TIMEOUT_SECONDS = 30;

    public static function isTest(): bool
    {
        return 'test' === ($_ENV['APP_ENV'] ?? null);
    }

    public static function reset(): void
    {
        $mark = self::readBridgeFileInt(self::MARK_SKIP_SINGLE_CAPTCHA_PATH);

        self::removeBridgeFile(self::MARK_SKIP_SINGLE_CAPTCHA_PATH);
        self::removeBridgeFile(self::MOCK_TIMESTAMP_PATH);

        if (null !== $mark) {
            throw new LogicException('Found unconsumed captcha skip mark');
        }
    }

    // ===== CAPTCHA MOCKING =====

    public static function setSkipSingleCaptcha(): void
    {
        if (!self::isTest()) {
            throw new LogicException('This must not be called outside tests');
        }

        if (null !== self::readBridgeFileInt(self::MARK_SKIP_SINGLE_CAPTCHA_PATH)) {
            throw new LogicException('Skipping captcha mark added twice');
        }

        self::writeBridgeFileInt(self::MARK_SKIP_SINGLE_CAPTCHA_PATH, time());
    }

    public static function shouldSkipSingleCaptcha(): bool // FIXME
    {
        if (!self::isTest()) {
            return false;
        }

        $timestamp = self::readBridgeFileInt(self::MARK_SKIP_SINGLE_CAPTCHA_PATH);

        if (null !== $timestamp && time() <= $timestamp + self::CAPTCHA_SKIP_TIMEOUT_SECONDS) {
            self::removeBridgeFile(self::MARK_SKIP_SINGLE_CAPTCHA_PATH);

            return true;
        }

        return false;
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
