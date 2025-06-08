<?php

declare(strict_types=1);

namespace App\Utils\Web\UrlStrategy;

use App\Utils\Regexp\Patterns;
use App\Utils\Web\FreeUrl;
use App\Utils\Web\Url;
use App\Utils\Web\UrlForTracking;
use Override;
use Symfony\Component\DomCrawler\Crawler;

/**
 * As of 2024-06-14 the Twitter's behavior is to send around 3700 bytes of the page,
 * often enough to cover the description and title.
 */
class TwitterProfileStrategy extends Strategy
{
    private const string profileUrlRegex = '^https://twitter\.com/(?<username>[^/?]+)$';

    #[Override]
    public static function isSuitableFor(string $url): bool
    {
        return Patterns::get(self::profileUrlRegex)->test($url);
    }

    public function getUrlForTracking(Url $url): Url
    {
        return new UrlForTracking($url, str_replace('https://twitter.com/', 'https://x.com/', $url->getUrl())); // Space Karen
    }

    #[Override]
    public function filterContents(string $input): string
    {
        $crawler = new Crawler($input);

        $ogDescriptionNodes = $crawler->filterXPath("//head/meta[@property='og:description']"); // grep-code-og-description-removal
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
        return new FreeUrl('https://x.com/'); // Space Karen
    }

    // TODO
    // private const string loginLocationHeader = "^location: \\S+login\\S+$"; //, setOf(RegexOption.MULTILINE, RegexOption.IGNORE_CASE))
    // override fun getLatentCode(url: Url, contents: String, originalCode: Int): Int {
    //     return if (originalCode == 302 && contents.contains(loginLocationHeader)) {
    //         401 // SPACE KAREN
    //     } else {
    //         originalCode
    //     }
    // }
}
