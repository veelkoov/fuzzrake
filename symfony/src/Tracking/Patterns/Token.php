<?php

declare(strict_types=1);

namespace App\Tracking\Patterns;

use App\Utils\ConfigurationException;
use App\Utils\Traits\UtilityClass;
use Veelkoov\Debris\Maps\StringToString;

final class Token
{
    use UtilityClass;

    private static ?StringToString $patternsCache = null;

    public static function getPattern(string $token): string
    {
        $cache = self::$patternsCache ??= new StringToString();

        return $cache->getOrSet($token, function () use ($token) {
            $start = str_starts_with($token, ' ') ? '' : '(?<=^|[^A-Z_])';
            $end = str_ends_with($token, ' ') ? '' : '(?=[^A-Z_]|$)';

            return '~'.$start.preg_quote($token, '~').$end.'~';
        });
    }

    /**
     * @return array{string, string}
     */
    public static function extractGroupName(string $token): array
    {
        if (!str_contains($token, '=')) {
            return [$token, ''];
        }

        $parts = explode('=', $token, 2);

        if (2 !== count($parts)) {
            throw new ConfigurationException("More than one '=' in token '$token'.");
        }

        return $parts;
    }
}
