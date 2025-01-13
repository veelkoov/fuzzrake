<?php

namespace App\Utils\Arrays;

use App\Utils\Traits\UtilityClass;

class Lists
{
    use UtilityClass;

    /**
     * @template T
     *
     * @param mixed[] $a1
     * @param mixed[] $a2
     *
     * @phpstan-return ($a1 is T[] ? ($a2 is T[] ? list<T> : list<mixed>) : list<mixed>)
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

    /**
     * @template T
     *
     * @param T[] $array
     *
     * @return list<T>
     */
    public static function unique(array $array): array
    {
        return array_values(array_unique($array));
    }
}
