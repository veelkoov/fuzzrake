<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\Url;
use Composer\Pcre\Preg;

abstract class Strategy
{
    // Seems to duplicate primary content on some serial websites.
    private const string META_DESCRIPTION_PATTERN = '~<meta (?:itemprop|property)="(?:og:|twitter:)?description"[^>]+>~';

    abstract public static function isSuitableFor(string $url): bool;

    public function filterContents(string $input): string
    {
        return Preg::replace(self::META_DESCRIPTION_PATTERN, '', $input);
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
