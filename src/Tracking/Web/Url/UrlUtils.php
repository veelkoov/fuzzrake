<?php

declare(strict_types=1);

namespace App\Tracking\Web\Url;

use App\Utils\Regexp\Patterns;
use App\Utils\Traits\UtilityClass;

use function Psl\Str\Byte\strip_prefix;

final class UrlUtils
{
    use UtilityClass;

    public static function hostFromUrl(string $url): string
    {
        return strip_prefix(parse_url($url, PHP_URL_HOST) ?: 'invalid_host', 'www.');
    }
}
