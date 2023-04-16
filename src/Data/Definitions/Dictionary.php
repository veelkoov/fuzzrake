<?php

declare(strict_types=1);

namespace App\Data\Definitions;

abstract class Dictionary
{
    /**
     * @return list<string>
     */
    abstract public static function getValues(): array;

    /**
     * @return array<string, string>
     */
    public static function getFormChoices(): array
    {
        return array_combine(static::getValues(), static::getValues());
    }
}
