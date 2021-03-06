<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Json;
use App\Utils\Regexp\Replacements;
use App\Utils\Web\WebsiteInfo;
use JsonException;
use Symfony\Component\DomCrawler\Crawler;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;

class HtmlPreprocessor
{
    private const HTML_CLEANER_REGEXPS = [
        '</?(strong|b|i|span|center|a|em|font)[^>]*>' => '',
        '(\s|&nbsp;|<br\s*/?>)+'                      => ' ',
        '<style[^>]*>.*?</style>'                     => '',
        ' style="[^"]*"( (?=\>))?'                    => '',
        '’|&\#39;|&\#8217;'                           => '\'',
        '<!--.*?-->'                                  => '',
        ' +data-[^>"]+ *= *"[^"]+" *'                 => ' ',
    ];

    private Replacements $cleanerReplacements;

    public function __construct()
    {
        $this->cleanerReplacements = new Replacements(self::HTML_CLEANER_REGEXPS, 's', '', '');
    }

    public static function processArtisansName(string $artisanName, string $inputText): string
    {
        $inputText = str_ireplace($artisanName, 'STUDIO_NAME', $inputText);
        if (strlen($artisanName) > 2 && 's' === strtolower(substr($artisanName, -1))) {
            /* Thank you, English language, I am enjoying this */
            $inputText = str_ireplace(substr($artisanName, 0, -1)."'s", 'STUDIO_NAME', $inputText);
        }

        return $inputText;
    }

    public function clean(string $inputText): string
    {
        $inputText = strtolower($inputText);
        $inputText = HtmlPreprocessor::extractFromJson($inputText);
        $inputText = $this->cleanerReplacements->do($inputText);

        return $inputText;
    }

    private static function extractFromJson(string $webpage): string
    {
        if (empty($webpage) || '{' !== $webpage[0]) {
            return $webpage;
        }

        try {
            $result = Json::decode($webpage);
        } catch (JsonException) {
            return $webpage;
        }

        return HtmlPreprocessor::flattenArray($result);
    }

    /**
     * https://stackoverflow.com/questions/1319903/how-to-flatten-a-multidimensional-array#comment7768057_1320156.
     */
    private static function flattenArray(array $array): string
    {
        $result = '';

        array_walk_recursive($array, function ($a, $b) use (&$result) {
            $result .= "$b: $a\n";
        });

        return $result;
    }

    /**
     * @throws TrackerException
     */
    public static function guessFilterFromUrl(string $url): string
    {
        try {
            return pattern('#(?<profile>.+)$')->match($url)
                ->findFirst(fn (Detail $match): string => $match->group('profile')->text())
                ->orReturn('');
        } catch (NonexistentGroupException $e) {
            throw new TrackerException('Regexp failed', exception: $e);
        }
    }

    /**
     * @throws TrackerException
     */
    public static function applyFilters(string $inputText, string $additionalFilter): string
    {
        if (WebsiteInfo::isFurAffinity(null, $inputText)) {
            if (WebsiteInfo::isFurAffinityUserProfile(null, $inputText)) {
                $additionalFilter = 'profile' === $additionalFilter ? 'td[width="80%"][align="left"]' : '';

                $crawler = new Crawler($inputText);
                $filtered = $crawler->filter('#page-userpage > tr:first-child > td:first-child > table.maintable > tr:first-child > td:first-child > table.maintable '.$additionalFilter);

                if (1 !== $filtered->count()) {
                    throw new TrackerException('Failed to filter FA profile, nodes count: '.$filtered->count());
                }

                return $filtered->html();
            }

            return $inputText;
        }

        if (WebsiteInfo::isTwitter($inputText)) {
            $crawler = new Crawler($inputText);
            $filtered = $crawler->filter('div.profileheadercard');

            if (1 !== $filtered->count()) {
                throw new TrackerException('Failed to filter Twitter profile, nodes count: '.$filtered->count());
            }

            return $filtered->html();
        }

        if (WebsiteInfo::isInstagram($inputText)) {
            $crawler = new Crawler($inputText);
            $filtered = $crawler->filter('script[type="application/ld+json"]');

            if (1 !== $filtered->count()) {
                throw new TrackerException('Failed to filter Instagram profile, nodes count: '.$filtered->count());
            }

            return $filtered->html();
        }

        return $inputText;
    }
}
