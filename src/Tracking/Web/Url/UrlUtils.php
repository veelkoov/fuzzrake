<?php

declare(strict_types=1);

namespace App\Tracking\Web\Url;

use App\Utils\Traits\UtilityClass;

use function Psl\Str\Byte\strip_prefix;

final class UrlUtils
{
    use UtilityClass;

    public static function hostFromUrl(string $url): string
    {
        return strip_prefix(parse_url($url, PHP_URL_HOST) ?: 'invalid_host', 'www.');
    }

    public static function safeFileNameFromUrl(string $url): string
    {
        $result = pattern('^https?://(www\.)?|(\?|#).+$', 'i')->prune($url);
        $result = pattern('[^a-z0-9_.-]+', 'i')->replace($result)->all()->with('_');

        return trim($result, '_');
    }
}
