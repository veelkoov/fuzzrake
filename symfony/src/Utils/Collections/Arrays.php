<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use Psl\Iter;

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
        if (1 !== count($input) || null === $result = Iter\first($input)) {
            throw new InvalidArgumentException('Given array does not have exactly one elements');
        }

        return $result;
    }

    /**
     * @param string[] $strings
     *
     * @return string[]
     */
    public static function nonEmptyStrings(array $strings): array
    {
        return array_filter($strings, static fn (string $string) => '' !== $string);
    }
}
