<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;
use DateTimeInterface;

final class DateTimeFormat
{
    use UtilityClass;

    private const DEFAULT_FORMAT = 'Y-m-d H:i';

    public static function fragileUtc(mixed $input, string $format = self::DEFAULT_FORMAT): string
    {
        if ($input instanceof DateTimeInterface) {
            return $input->format($format) ?: 'unknown/error';
        } else {
            return 'unknown/error';
        }
    }

    public static function nullableUtc(mixed $input, string $format = self::DEFAULT_FORMAT): string
    {
        return null === $input ? 'never' : self::fragileUtc($input, $format);
    }
}
