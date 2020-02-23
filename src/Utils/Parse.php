<?php

declare(strict_types=1);

namespace App\Utils;

abstract class Parse
{
    public static function tInt($input): int
    {
        if (is_int($input)) {
            return $input;
        }

        if (is_string($input)) {
            $input = trim($input);
        }

        return self::int($input ?? '');
    }

    public static function int($input): int
    {
        if (is_int($input)) {
            return $input;
        }

        $input = $input ?? '';

        $result = (int) $input;

        if ((string) $result !== $input) {
            throw new ParseException("'$input' is not a valid integer");
        }

        return $result;
    }

    public static function tPercentAsInt(?string $input): int
    {
        return self::percentAsInt(trim($input ?? ''));
    }

    public static function percentAsInt(?string $input): int
    {
        $input = $input ?? '';

        $result = (int) substr($input, 0, -1);

        if ((string) $result.'%' !== $input) {
            throw new ParseException("'$input' is not a valid percent integer");
        }

        return $result;
    }

    public static function tFloat(?string $input): float
    {
        return self::float(trim($input ?? ''));
    }

    public static function float(?string $input): float
    {
        $input = $input ?? '';

        $result = (float) $input;

        if ('-' === substr($input, 0, 1)) {
            $input = substr($input, 1);
        }

        if (trim($input, '.') !== $input || 0 === strlen($input)
            || '' !== trim($input, '1234567890.') || substr_count($input, '.') > 1) {
            throw new ParseException("'$input' is not a valid float");
        }

        return $result;
    }
}
