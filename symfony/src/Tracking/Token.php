<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Utils\ConfigurationException;
use App\Utils\Traits\UtilityClass;
use TRegx\CleanRegex\Pattern;

final class Token
{
    use UtilityClass;

    private static ?StringToPattern $patternsCache = null;

    public static function getPattern(string $token): Pattern
    {
        $cache = self::$patternsCache ??= new StringToPattern();

        return $cache->getOrSet($token, function () use ($token) {
            $start = str_starts_with($token, ' ') ? '' : '(?<=^|[^A-Z_])';
            $end = str_ends_with($token, ' ') ? '' : '(?=[^A-Z_]|$)';

            return Pattern::inject("$start@$end", [$token]);
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
