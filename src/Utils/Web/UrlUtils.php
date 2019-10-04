<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Regexp\Utils as Regexp;

class UrlUtils
{
    private function __construct()
    {
    }

    public static function hostFromUrl(string $url): string
    {
        return Regexp::replace('#^www\.#', '', parse_url($url, PHP_URL_HOST) ?: 'invalid_host');
    }
}
