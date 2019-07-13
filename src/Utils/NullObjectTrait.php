<?php

declare(strict_types=1);

namespace App\Utils;

use RuntimeException;

trait NullObjectTrait
{
    private static $me = null;

    public static function get(): self
    {
        return self::$me ?? self::$me = new self();
    }

    protected static function throwIncomplete(): void
    {
        throw new RuntimeException('Null object incomplete');
    }
}
