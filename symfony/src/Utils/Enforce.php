<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

final class Enforce // TODO: Improve https://github.com/veelkoov/fuzzrake/issues/221
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
     * @return list<string>
     */
    public static function strList(mixed $input): array
    {
        if (!StringList::isValid($input)) {
            throw new InvalidArgumentException('Expected a list of strings');
        }

        return $input;
    }

    public static function bool(mixed $input): bool
    {
        if (!is_bool($input)) {
            throw new InvalidArgumentException('Expected bool, got '.get_debug_type($input));
        }

        return $input;
    }

    public static function nBool(mixed $input): ?bool
    {
        if (null === $input) {
            return null;
        }

        return self::bool($input);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return array<T>
     */
    public static function arrayOf(mixed $input, string $class): array
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException("Expected an array of $class");
        }

        foreach ($input as $item) {
            Enforce::objectOf($item, $class);
        }

        return $input;
    }

    /**
     * @return array<mixed>
     */
    public static function array(mixed $input): array
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException('Expected an array');
        }

        return $input;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function objectOf(mixed $input, string $class): mixed
    {
        if (is_object($input) && (is_a($input, $class) || is_subclass_of($input, $class))) {
            return $input;
        }

        throw new InvalidArgumentException("Expected object of class $class, got ".get_debug_type($input));
    }

    public static function int(mixed $input): int
    {
        if (!is_int($input)) {
            throw new InvalidArgumentException('Expected an integer');
        }

        return $input;
    }
}
