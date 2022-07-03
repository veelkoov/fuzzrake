<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;

final class Arrays
{
    use UtilityClass;

    public static function assoc(array $input, int|string $key = 0, int|string $value = 1): array
    {
        $result = [];

        foreach ($input as $item) {
            $result[$item[$key]] = $item[$value];
        }

        return $result;
    }

    public static function intersect(array $a1, array $a2): array
    {
        $result = $a1;

        foreach ($result as $key => $value) {
            if (!in_array($value, $a2, true)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    public static function isArrayOfStrings(mixed $value): bool
    {
        return !is_array($value) || !array_reduce($value, fn ($prev, $item) => $prev && is_string($item), true);
    }
}
