<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\StrUtils;
use App\Utils\Traits\UtilityClass;
use App\Utils\UnbelievableRuntimeException;
use DateTimeImmutable;

class NewArtisan
{
    use UtilityClass;

    public static function getCutoffDate(): DateTimeImmutable
    {
        try {
            return UtcClock::at('-42 days'); // grep-amount-of-days-considered-new
        } catch (DateTimeException $e) { // @codeCoverageIgnoreStart
            throw new UnbelievableRuntimeException($e);
        } // @codeCoverageIgnoreEnd
    }

    public static function getCutoffDateStr(): string
    {
        return StrUtils::asStr(self::getCutoffDate());
    }
}
