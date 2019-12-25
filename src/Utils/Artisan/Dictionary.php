<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

abstract class Dictionary
{
    protected static $valueKeyMap = null;
    protected static $keyKeyMap = null;

    abstract public static function getValues(): array;

    public static function getKeys(): array
    {
        return array_keys(static::getValues());
    }

    public static function getKeyKeyMap(): array
    {
        return self::$keyKeyMap ?? self::$keyKeyMap = array_combine(self::getKeys(), self::getKeys());
    }

    public static function getValueKeyMap(): array
    {
        return self::$valueKeyMap ?? self::$valueKeyMap = array_flip(static::getValues());
    }

    public static function getValuesAsString(): string
    {
        return implode("\n", static::getValues());
    }

    public static function count(): int
    {
        return count(static::getValues());
    }
}
