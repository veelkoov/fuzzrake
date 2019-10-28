<?php

declare(strict_types=1);

namespace App\Utils;

class Parse
{
    private function __construct()
    {
    }

    public static function tInt(string $input): int
    {
        return self::int(trim($input));
    }

    public static function int(string $input): int
    {
        $result = (int) $input;

        if ((string) $result !== $input) {
            throw new ParseException("'$input' is not a valid integer");
        }

        return $result;
    }

    public static function tPercentAsInt(string $input): int
    {
        return self::percentAsInt(trim($input));
    }

    public static function percentAsInt(string $input): int
    {
        $result = (int) substr($input, 0, -1);

        if ((string) $result.'%' !== $input) {
            throw new ParseException("'$input' is not a valid percent integer");
        }

        return $result;
    }

    public static function tFloat(string $input): float
    {
        return self::float(trim($input));
    }

    public static function float(string $input): float
    {
        $result = (float) $input;

        if ('-' === substr($input, 0, 1)) {
            $input = substr($input, 1);
        }

        if (false !== strpos($input, '.') && (strlen($input) < 3 || '.' !== trim($input, '1234567890'))) {
            throw new ParseException("'$input' is not a valid float");
        }

        return $result;
    }
}
