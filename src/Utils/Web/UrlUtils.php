<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Traits\UtilityClass;

final class UrlUtils
{
    use UtilityClass;

    public static function hostFromUrl(string $url): string
    {
        return pattern('^www\.')->remove(parse_url($url, PHP_URL_HOST) ?: 'invalid_host')->first();
    }

    public static function safeFileNameFromUrl(string $url): string
    {
        $result = pattern('^https?://(www\.)?|(\?|#).+$', 'i')->remove($url)->all();
        $result = pattern('[^a-z0-9_.-]+', 'i')->replace($result)->all()->with('_');

        return trim($result, '_');
    }
}
