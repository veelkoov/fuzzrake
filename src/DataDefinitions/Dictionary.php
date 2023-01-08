<?php

declare(strict_types=1);

namespace App\DataDefinitions;

abstract class Dictionary
{
    /**
     * @return list<string>
     */
    abstract public static function getValues(): array;
}
