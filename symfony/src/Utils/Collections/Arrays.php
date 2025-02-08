<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

use function Psl\Iter\first;

final class Arrays
{
    use UtilityClass;

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
