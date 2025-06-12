<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;

final class UrlUtils
{
    use UtilityClass;

    public static function getHost(string $url): string
    {
        $result = parse_url($url, PHP_URL_HOST);

        return is_string($result)
            ? $result
            : throw new InvalidArgumentException("Failed to parse the host name from the URL: $url.");
    }
}
