<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Traits\UtilityClass;

final class UrlUtils
{
    use UtilityClass;

    public static function hostFromUrl(string $url): string
    {
        return pattern('^www\.')->prune(parse_url($url, PHP_URL_HOST) ?: 'invalid_host');
    }

    public static function safeFileNameFromUrl(string $url): string
    {
        $result = pattern('^https?://(www\.)?|(\?|#).+$', 'i')->prune($url);
        $result = pattern('[^a-z0-9_.-]+', 'i')->replace($result)->all()->with('_');

        return trim($result, '_');
    }
}
