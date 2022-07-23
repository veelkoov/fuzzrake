<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\TestUtils\TestsBridge;
use App\Utils\TestUtils\UtcClockMock;
use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use RuntimeException;

final class UtcClock
{
    use UtilityClass;

    public static function now(): DateTimeImmutable
    {
        $result = DateTimeImmutable::createFromFormat('U', (string) self::time());

        if (false === $result) {
            throw new RuntimeException('Failed to parse "U" date');
        }

        return $result->setTimezone(self::getUtc());
    }

    public static function getUtc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    /**
     * @throws DateTimeException
     */
    public static function at(string|false|null $time): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($time ?: 'invalid', self::getUtc());
        } catch (Exception $e) {
            throw new DateTimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function getMonthLaterYmd(): string
    {
        return date('Y-m-d', strtotime('+1 month', self::time()));
    }

    public static function getWeekLaterYmd(): string
    {
        return date('Y-m-d', strtotime('+1 week', self::time()));
    }

    public static function getTomorrowYmd(): string
    {
        return date('Y-m-d', strtotime('+1 day', self::time()));
    }

    public static function passed(DateTimeImmutable $dateTime): bool
    {
        return self::now() > $dateTime;
    }

    public static function timems(): int
    {
        return TestsBridge::isTest() ? UtcClockMock::timems() : (int) (microtime(true) * 1000);
    }

    public static function time(): int
    {
        return TestsBridge::isTest() ? UtcClockMock::time() : time();
    }
}
