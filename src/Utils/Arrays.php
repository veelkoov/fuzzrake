<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;

final class Arrays
{
    use UtilityClass;

    public static function assoc(array $input, $key = 0, $value = 1): array
    {
        $result = [];

        foreach ($input as $item) {
            $result[$item[$key]] = $item[$value];
        }

        return $result;
    }
}
