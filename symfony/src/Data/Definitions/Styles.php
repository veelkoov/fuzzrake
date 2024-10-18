<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

class Styles extends Dictionary
{
    final public const string TOONY = 'Toony';
    final public const string SEMI_TOONY = 'Semi Toony';
    final public const string SEMI_REALISTIC = 'Semi Realistic';
    final public const string REALISTIC = 'Realistic';
    final public const string KEMONO = 'Kemono';
    final public const string KIGURUMI = 'Kigurumi';
    final public const string ANIME = 'Anime';

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
