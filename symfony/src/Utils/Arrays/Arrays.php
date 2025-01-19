<?php

declare(strict_types=1);

namespace App\Utils\Arrays;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

use function Psl\Iter\first;

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
     * @template T
     *
     * @param T[] $input
     *
     * @return T
     *
     * @throws InvalidArgumentException
     */
    public static function single(array $input): mixed
    {
        if (1 !== count($input) || null === $result = first($input)) {
            throw new InvalidArgumentException('Given array does not have exactly one elements');
        }

        return $result;
    }
}
