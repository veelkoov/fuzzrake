<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

abstract class DateTimeUtils
{
    public static function getNowUtc(): DateTime
    {
        try {
            return DateTime::createFromFormat('U', (string) time(), self::getUtc());
        } catch (Exception $e) {
            throw new RuntimeDateTimeException($e);
        }
    }

    public static function getUtc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    /**
     * @throws DateTimeException
     */
    public static function getUtcAt(?string $time): DateTime
    {
        try {
            return new DateTime($time ?: 'invalid', self::getUtc());
        } catch (Exception $e) {
            throw new DateTimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function getMonthLaterYmd(): string
    {
        return date('Y-m-d', strtotime('+1 month'));
    }

    public static function getWeekLaterYmd(): string
    {
        return date('Y-m-d', strtotime('+1 week'));
    }

    public static function getTomorrowYmd(): string
    {
        return date('Y-m-d', strtotime('+1 day'));
    }

    public static function passed(DateTimeInterface $dateTime): bool
    {
        return self::getNowUtc() > $dateTime;
    }

    public static function timems(): int
    {
        return (int) (microtime(true) * 1000);
    }
}
