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
        return new DateTime('now', new DateTimeZone('UTC'));
    }
}
