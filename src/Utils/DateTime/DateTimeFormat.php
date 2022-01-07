<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;
use DateTimeInterface;

final class DateTimeFormat
{
    use UtilityClass;

    private const FORMAT = 'Y-m-d H:i T'; // grep-expected-utc-datetime-format

    public static function fragileUtc(mixed $input): string
    {
        if ($input instanceof DateTimeInterface) {
            return $input->format(self::FORMAT) ?: 'unknown/error';
        } else {
            return 'unknown/error';
        }
    }

    public static function nullableUtc(mixed $input): string
    {
        return null === $input ? 'never' : self::fragileUtc($input);
    }
}
