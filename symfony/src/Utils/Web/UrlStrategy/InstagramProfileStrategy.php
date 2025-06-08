<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Regexp\Patterns;
use App\Utils\Web\Url\Url;
use App\Utils\Web\Url\UrlForTracking;
use Override;
use Symfony\Component\DomCrawler\Crawler;

class InstagramProfileStrategy extends Strategy
{
    private const string instagramProfileUrl = '^https?://(www\.)?instagram\.com/(?<username>[^/]+)/?$';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Patterns::get(self::instagramProfileUrl)->test($url);
    }

    #[Override]
    public function getUrlForTracking(Url $url): Url
    {
        $match = Patterns::get(self::instagramProfileUrl)->match($url->getUrl());

        if ($match->fails()) {
            return $url;
        }

        return new UrlForTracking("https://www.instagram.com/{$match->first()->get('username')}/profilecard/", $url);
    }

    #[Override]
    public function filterContents(string $input): string
    {
        $descriptionNodes = (new Crawler($input))->filterXPath("//head/meta[@property='description']");

        if (0 === $descriptionNodes->count()) {
            return $input;
        }

        return $descriptionNodes->attr('content') ?? '';
    }
}
