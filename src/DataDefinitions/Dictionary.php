<?php

declare(strict_types=1);

namespace App\DataDefinitions;

abstract class Dictionary
{
    /**
     * @var array<string, string>
     */
    protected static ?array $valueKeyMap = null;

    /**
     * @var array<string, string>
     */
    protected static ?array $keyKeyMap = null;

    /**
     * @return array<string, string>
     */
    abstract public static function getValues(): array;

    /**
     * @return string[]
     */
    public static function getKeys(): array
    {
        return array_keys(static::getValues());
    }

    /**
     * @return array<string, string>
     */
    public static function getKeyKeyMap(): array
    {
        return self::$keyKeyMap ?? self::$keyKeyMap = array_combine(self::getKeys(), self::getKeys());
    }

    /**
     * @return array<string, string>
     */
    public static function getValueKeyMap(): array
    {
        return self::$valueKeyMap ?? self::$valueKeyMap = array_flip(static::getValues());
    }
}
