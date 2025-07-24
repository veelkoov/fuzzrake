<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\FreeUrl;
use App\Utils\Web\Url\Url;
use App\Utils\Web\Url\UrlForTracking;
use Composer\Pcre\Preg;
use Override;
use Symfony\Component\DomCrawler\Crawler;

/**
 * As of 2024-06-14, Twitter's behavior is to send around 3700 bytes of the page,
 * often enough to cover the description and title.
 */
class TwitterProfileStrategy extends Strategy
{
    private const string profileUrlRegex = '~^https://(twitter|x)\.com/(?<username>[^/?]+)$~';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Preg::isMatch(self::profileUrlRegex, $url);
    }

    #[Override]
    public function getUrlForTracking(Url $url): Url
    {
        return new UrlForTracking(str_replace('https://twitter.com/', 'https://x.com/', $url->getUrl()), $url); // Space Karen
    }

    #[Override]
    public function filterContents(string $input): string
    {
        $crawler = new Crawler($input);

        $ogDescriptionNodes = $crawler->filterXPath("//head/meta[@property='og:description']");
        $ogTitleNodes = $crawler->filterXPath("//head/meta[@property='og:title']");

        if (0 === $ogDescriptionNodes->count() || 0 === $ogTitleNodes->count()) {
            return $input;
        }

        $ogTitle = $ogTitleNodes->attr('content') ?? '';
        $ogDescription = $ogDescriptionNodes->attr('content') ?? '';

        return "$ogTitle\n$ogDescription";
    }

    public function getCookieInitUrl(): Url
    {
        return new FreeUrl('https://x.com/', ''); // Space Karen
    }
}
