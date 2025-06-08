<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\Url;

abstract class Strategy
{
    abstract public static function isSuitableFor(string $url): bool;

    public function filterContents(string $input): string
    {
        return $input;
    }

    public function getUrlForTracking(Url $url): Url
    {
        return $url;
    }

    public function getCookieInitUrl(): ?Url
    {
        return null;
    }

    public function getLatentCode(Url $url, string $contents, int $originalCode): int
    {
        return $originalCode;
    }
}
