<?php

declare(strict_types=1);

namespace App\DataDefinitions;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StrUtils;
use App\Utils\Traits\UtilityClass;
use App\Utils\UnbelievableRuntimeException;
use DateTimeInterface;

class NewArtisan
{
    use UtilityClass;

    public static function getCutoffDate(): DateTimeInterface
    {
        try {
            return DateTimeUtils::getUtcAt('-42 days'); // grep-amount-of-days-considered-new
        } catch (DateTimeException $e) {
            throw new UnbelievableRuntimeException($e);
        }
    }

    public static function getCutoffDateStr(): string
    {
        return StrUtils::asStr(self::getCutoffDate());
    }
}
