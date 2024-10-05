<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;
use DateTimeImmutable;

final class DateTimeFormat
{
    use UtilityClass;

    private const string DEFAULT_FORMAT = 'Y-m-d H:i';

    public static function fragileUtc(mixed $input, string $format = self::DEFAULT_FORMAT): string
    {
        if ($input instanceof DateTimeImmutable) {
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
