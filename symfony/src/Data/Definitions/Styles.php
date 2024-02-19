<?php

declare(strict_types=1);

namespace App\Data\Definitions;

class Styles extends Dictionary
{
    final public const TOONY = 'Toony';
    final public const SEMI_TOONY = 'Semi Toony';
    final public const SEMI_REALISTIC = 'Semi Realistic';
    final public const REALISTIC = 'Realistic';
    final public const KEMONO = 'Kemono';
    final public const KIGURUMI = 'Kigurumi';
    final public const ANIME = 'Anime';

    public static function getValues(): array
    {
        return [
            self::TOONY,
            self::SEMI_TOONY,
            self::SEMI_REALISTIC,
            self::REALISTIC,
            self::KEMONO,
            self::KIGURUMI,
            self::ANIME,
        ];
    }
}
