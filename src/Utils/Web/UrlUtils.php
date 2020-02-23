<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Regexp\Regexp;

abstract class UrlUtils
{
    private const REPLACED_UNSAFE_CHARACTERS = '#[^a-z0-9_.-]+#i';
    private const REMOVED_BEGINNING_AND_END = '~^https?://(www\.)?|(\?|#).+$~';

    public static function hostFromUrl(string $url): string
    {
        return Regexp::replace('#^www\.#', '', parse_url($url, PHP_URL_HOST) ?: 'invalid_host');
    }

    public static function safeFileNameFromUrl(string $url): string
    {
        return trim(Regexp::replace(self::REPLACED_UNSAFE_CHARACTERS, '_',
            Regexp::replace(self::REMOVED_BEGINNING_AND_END, '', $url)
        ), '_');
    }
}
