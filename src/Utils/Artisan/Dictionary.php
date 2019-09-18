<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

abstract class Dictionary
{
    protected static $flipped = null;

    abstract public static function getAllValues(): array;

    public static function getValueKeyMap(): array
    {
        return static::$flipped ?? static::$flipped = array_flip(static::getAllValues());
    }

    public static function getAllValuesAsString(): string
    {
        return implode("\n", static::getAllValues());
    }

    public static function count(): int
    {
        return count(static::getAllValues());
    }
}
