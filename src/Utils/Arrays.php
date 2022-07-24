<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;

final class Arrays
{
    use UtilityClass;

    /**
     * @template K of int|string
     *
     * @param array<mixed[]> $input
     * @param K              $key
     * @param K              $value
     *
     * @return array<mixed, mixed>
     */
    public static function assoc(array $input, int|string $key = 0, int|string $value = 1): array
    {
        $result = [];

        foreach ($input as $item) {
            $nKey = $item[$key];
            $nValue = $item[$value];

            $result[$nKey] = $nValue;
        }

        return $result;
    }

    /**
     * @param mixed[] $a1
     * @param mixed[] $a2
     *
     * @return mixed[]
     */
    public static function intersect(array $a1, array $a2): array
    {
        $result = $a1;

        foreach ($result as $key => $value) {
            if (!in_array($value, $a2, true)) {
                unset($result[$key]);
            }
        }

        return array_values($result);
    }
}
