<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

abstract class Dictionary
{
    abstract public static function getAllValues(): array;

    public static function getAllValuesAsString(): string
    {
        return implode("\n", self::getAllValues());
    }
}
