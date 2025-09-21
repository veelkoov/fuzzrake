<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\Url;
use App\Utils\Web\Url\UrlForTracking;
use Composer\Pcre\Regex;
use Override;
use Symfony\Component\DomCrawler\Crawler;

class InstagramProfileStrategy extends Strategy
{
    private const string instagramProfileUrl = '~^https?://(www\.)?instagram\.com/(?<username>[^/]+)/?$~n';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Regex::isMatch(self::instagramProfileUrl, $url);
    }

    #[Override]
    public function getUrlForTracking(Url $url): Url
    {
        $match = Regex::matchStrictGroups(self::instagramProfileUrl, $url->getUrl());

        if (!$match->matched) {
            return $url;
        }

        return new UrlForTracking("https://www.instagram.com/{$match->matches['username']}/profilecard/", $url);
    }

    #[Override]
    public function filterContents(string $input): string
    {
        $descriptionNodes = new Crawler($input)->filterXPath("//head/meta[@property='description']");

        if (0 === $descriptionNodes->count()) {
            return $input;
        }

        return $descriptionNodes->attr('content') ?? '';
    }
}
