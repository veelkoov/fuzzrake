<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

trait NullObjectTrait
{
    private static ?self $me = null;

    public static function get(): static
    {
        return self::$me ?? self::$me = new static();
    }

    protected static function incomplete(): RuntimeException
    {
        throw new RuntimeException('Null object incomplete');
    }
}
