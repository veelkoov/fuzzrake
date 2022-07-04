<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;

/**
 * Methods prefixes:
 *   t - trims input (if string provided as the argument),
 *   n - returns null instead of throwing exception on failure.
 */
final class Parse
{
    use UtilityClass;

    public static function tInt(mixed $input): int
    {
        if (is_int($input)) {
            return $input;
        }

        if (is_string($input)) {
            $input = trim($input);
        }

        return self::int($input ?? '');
    }

    public static function int(mixed $input): int
    {
        if (is_int($input)) {
            return $input;
        }

        $input ??= '';

        $result = (int) $input;

        if ((string) $result !== $input) {
            throw new ParseException("'$input' is not a valid integer");
        }

        return $result;
    }

    public static function nBool(string $value): ?bool
    {
        $value = '' === $value ? 'null' : $value; // Return null for empty string as well

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }
}
