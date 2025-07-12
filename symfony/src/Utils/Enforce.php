<?php

declare(strict_types=1);

namespace App\Utils;

use App\Utils\Collections\StringLists;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

final class Enforce // TODO: Improve https://github.com/veelkoov/fuzzrake/issues/221
{
    use UtilityClass;

    /**
     * @phpstan-assert string $input
     */
    public static function string(mixed $input): string
    {
        if (!is_string($input)) {
            throw new InvalidArgumentException('Expected string, got '.get_debug_type($input));
        }

        return $input;
    }

    /**
     * @phpstan-assert string|null $input
     */
    public static function nString(mixed $input): ?string
    {
        if (null === $input) {
            return null;
        }

        return self::string($input);
    }

    /**
     * @return list<string>
     *
     * @phpstan-assert list<string> $input
     */
    public static function strList(mixed $input): array
    {
        if (!StringLists::isValid($input)) {
            throw new InvalidArgumentException('Expected a list of strings');
        }

        return $input;
    }

    /**
     * @phpstan-assert bool $input
     */
    public static function bool(mixed $input): bool
    {
        if (!is_bool($input)) {
            throw new InvalidArgumentException('Expected bool, got '.get_debug_type($input));
        }

        return $input;
    }

    /**
     * @phpstan-assert bool|null $input
     */
    public static function nBool(mixed $input): ?bool
    {
        if (null === $input) {
            return null;
        }

        return self::bool($input);
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return array<T>
     *
     * @phpstan-assert array<T> $input
     */
    public static function arrayOf(mixed $input, string $class): array
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException("Expected an array of $class");
        }

        return arr_map($input, static fn ($item) => Enforce::objectOf($item, $class));
    }

    /**
     * @return array<mixed>
     *
     * @phpstan-assert array<mixed> $input
     */
    public static function array(mixed $input): array
    {
        if (!is_array($input)) {
            throw new InvalidArgumentException('Expected an array');
        }

        return $input;
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @phpstan-assert T $input
     */
    public static function objectOf(mixed $input, string $class): mixed
    {
        if (!($input instanceof $class)) {
            throw new InvalidArgumentException("Expected object of class $class, got ".get_debug_type($input));
        }

        return $input;
    }

    /**
     * @phpstan-assert int $input
     */
    public static function int(mixed $input): int
    {
        if (!is_int($input)) {
            throw new InvalidArgumentException('Expected an integer');
        }

        return $input;
    }
}
