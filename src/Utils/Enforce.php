<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

final class Enforce
{
    use UtilityClass;

    public static function string(mixed $input): string
    {
        if (!is_string($input)) {
            throw new InvalidArgumentException('Expected string, got '.get_debug_type($input));
        }

        return $input;
    }

    public static function nString(mixed $input): ?string
    {
        if (null === $input) {
            return null;
        }

        return self::string($input);
    }

    /**
     * @return string[]
     */
    public static function strList(mixed $input): array
    {
        if (!is_array($input) || !array_is_list($input) || !array_reduce($input, fn ($prev, $item) => $prev && is_string($item), true)) {
            throw new InvalidArgumentException('Expected array of strings');
        }

        return $input;
    }
}
