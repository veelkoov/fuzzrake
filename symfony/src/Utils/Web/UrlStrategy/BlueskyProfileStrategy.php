<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use Composer\Pcre\Preg;
use Override;
use Symfony\Component\DomCrawler\Crawler;

class BlueskyProfileStrategy extends Strategy
{
    private const string profileUrlRegex = '~^https://bsky\.app/profile/[^/?]+$~';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Preg::isMatch(self::profileUrlRegex, $url);
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
}
