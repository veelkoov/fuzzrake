<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

final class Styles extends Dictionary
{
    public const string TOONY = 'Toony';
    public const string SEMI_TOONY = 'Semi Toony';
    public const string SEMI_REALISTIC = 'Semi Realistic';
    public const string REALISTIC = 'Realistic';
    public const string KEMONO = 'Kemono';
    public const string KIGURUMI = 'Kigurumi';
    public const string ANIME = 'Anime';

    #[Override]
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
