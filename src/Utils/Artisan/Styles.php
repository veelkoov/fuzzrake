<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class Styles extends Dictionary
{
    public const TOONY = 'Toony';
    public const SEMI_TOONY = 'Semi Toony';
    public const SEMI_REALISTIC = 'Semi Realistic';
    public const REALISTIC = 'Realistic';
    public const KEMONO = 'Kemono';
    public const KIGURUMI = 'Kigurumi';
    public const ANIME = 'Anime';

    public static function getAllValues(): array
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
