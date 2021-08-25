<?php

declare(strict_types=1);

namespace App\Utils\DateTime;

use App\Utils\Traits\UtilityClass;
use DateTimeInterface;

final class DateTimeFormat
{
    use UtilityClass;

    public static function fragile($input, string $format = 'Y-m-d H:i'): string
    {
        if ($input instanceof DateTimeInterface) {
            return $input->format($format) ?: 'unknown/error';
        } else {
            return 'unknown/error';
        }
    }

    public static function nullable($input, string $format = 'Y-m-d H:i'): string
    {
        if (null === $input) {
            return 'never';
        } elseif ($input instanceof DateTimeInterface) {
            return $input->format($format) ?: 'unknown/error';
        } else {
            return 'unknown/error';
        }
    }
}
