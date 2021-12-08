<?php

declare(strict_types=1);

namespace App\DataDefinitions;

abstract class Dictionary
{
    protected static ?array $valueKeyMap = null;
    protected static ?array $keyKeyMap = null;

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
}
