<?php

declare(strict_types=1);

namespace App\Utils;

use DateTime;
use DateTimeZone;
use Exception;

class DateTimeUtils
{
    public static function getNowUtc(): DateTime
    {
        try {
            return new DateTime('now', self::getUtc());
        } catch (Exception $e) {
            throw new RuntimeDateTimeException($e);
        }
    }

    public static function getUtc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }

    /**
     * @param string $time
     *
     * @return DateTime
     *
     * @throws DateTimeException
     */
    public static function getUtcAt(string $time): DateTime
    {
        try {
            return new DateTime($time, self::getUtc());
        } catch (Exception $e) {
            throw new DateTimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function getMonthLaterYmd(): string
    {
        return date('Y-m-d', strtotime('+1 month'));
    }

    public static function getWeekLaterYmd()
    {
        return date('Y-m-d', strtotime('+1 week'));
    }

    public static function passed(DateTime $dateTime): bool
    {
        return self::getNowUtc() > $dateTime;
    }

    public static function timems(): int
    {
        return (int) (microtime(true) * 1000);
    }
}
