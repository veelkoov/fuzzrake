<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Utils\Traits\UtilityClass;

final class StringLists // TODO: Improve https://github.com/veelkoov/fuzzrake/issues/221
{
    use UtilityClass;

    /**
     * @param list<string> $input1
     * @param list<string> $input2
     */
    public static function sameElements(array $input1, array $input2): bool
    {
        if (count($input1) !== count($input2)) {
            return false;
        }

        sort($input1);
        sort($input2);

        return $input1 === $input2;
    }

    /**
     * @phpstan-assert-if-true list<string> $input
     */
    public static function isValid(mixed $input): bool
    {
        return is_array($input) && array_is_list($input)
            && array_reduce($input, fn ($prev, $item) => $prev && is_string($item), true);
    }
}
