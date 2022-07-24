<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\TestUtils\TestsBridge;
use App\Utils\TestUtils\UtcClockMock;
use App\Utils\Traits\UtilityClass;
use App\Utils\UnbelievableRuntimeException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

final class UtcClock
{
    use UtilityClass;

    public static function getUtc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    public static function now(): DateTimeImmutable
    {
        try {
            return self::fromTimestamp(self::time());
        } catch (DateTimeException $exception) {
            throw new UnbelievableRuntimeException($exception);
        }
    }

    /**
     * @throws DateTimeException
     */
    public static function at(string|false|null $time): DateTimeImmutable
    {
        $timestamp = strtotime((string) $time, self::time());

        if (false === $timestamp) {
            throw new DateTimeException("Failed to parse timestamp from input: '$time'");
        }

        try {
            return self::fromTimestamp($timestamp);
        } catch (DateTimeException $exception) {
            throw new DateTimeException("Failed to create DateTime from input: '$time'", previous: $exception);
        }
    }

    /**
     * @throws DateTimeException
     */
    public static function fromTimestamp(int $timestamp): DateTimeImmutable
    {
        try {
            return (new DateTimeImmutable("@$timestamp"))->setTimezone(self::getUtc());
        } catch (Exception $exception) {
            throw new DateTimeException("Failed to create DateTime from timestamp: $timestamp", previous: $exception);
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
