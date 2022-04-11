<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;
use App\Utils\UnbelievableRuntimeException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

final class UtcClock
{
    use UtilityClass;

    public static function now(): DateTimeImmutable
    {
        try {
            return DateTimeImmutable::createFromFormat('U', (string) self::time())->setTimezone(self::getUtc());
        } catch (Exception $e) {
            throw new UnbelievableRuntimeException($e);
        }
    }

    public static function getUtc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    /**
     * @throws DateTimeException
     */
    public static function at(?string $time): DateTimeImmutable
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
        return self::isTest() ? UtcClockForTests::timems() : (int) (microtime(true) * 1000);
    }

    public static function time(): int
    {
        return self::isTest() ? UtcClockForTests::time() : time();
    }

    private static function isTest(): bool
    {
        return 'test' === ($_ENV['APP_ENV'] ?? null);
    }
}
