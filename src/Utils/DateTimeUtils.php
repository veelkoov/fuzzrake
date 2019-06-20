<?php

declare(strict_types=1);

namespace App\Utils;

use DateTime;
use DateTimeZone;
use Exception;

class DateTimeUtils
{
    /**
     * @return DateTime
     *
     * @throws Exception
     */
    public static function getNowUtc(): DateTime
    {
        return new DateTime('now', self::getUtc());
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
     * @throws Exception
     */
    public static function getUtcAt(string $time): DateTime
    {
        return new DateTime($time, self::getUtc());
    }

    public static function getMonthLaterYmd(): string
    {
        return date('Y-m-d', strtotime('+1 month'));
    }

    /**
     * @param DateTime $dateTime
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function passed(DateTime $dateTime): bool
    {
        return self::getNowUtc() > $dateTime;
    }
}
