<?php

declare(strict_types=1);

namespace App\Utils\Traits;

trait Singleton
{
    private static ?self $INSTANCE = null;

    private function __construct()
    {
    }

    public static function getInstance(): static
    {
        return self::$INSTANCE ?? self::$INSTANCE = new static();
    }
}
