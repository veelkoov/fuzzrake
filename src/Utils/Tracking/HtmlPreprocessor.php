<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\Regexp;
use App\Utils\Web\WebsiteInfo;
use Symfony\Component\DomCrawler\Crawler;

abstract class HtmlPreprocessor
{
    public static function processArtisansName(string $artisanName, string $inputText): string
    {
        $inputText = str_ireplace($artisanName, 'STUDIO_NAME', $inputText);
        if (strlen($artisanName) > 2 && 's' === strtolower(substr($artisanName, -1))) {
            /* Thank you, English language, I am enjoying this */
            $inputText = str_ireplace(substr($artisanName, 0, -1)."'s", 'STUDIO_NAME', $inputText);
        }

        return $inputText;
    }

    public static function cleanHtml(string $inputText): string
    {
        $inputText = strtolower($inputText);
        $inputText = HtmlPreprocessor::extractFromJson($inputText);
        $inputText = Regexp::replaceAll(CommissionsStatusRegexps::HTML_CLEANER_REGEXPS, $inputText);

        return $inputText;
    }

    private static function extractFromJson(string $webpage): string
    {
        if (empty($webpage) || '{' !== $webpage[0]) {
            return $webpage;
        }

        $result = json_decode($webpage, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
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

    public static function guessFilterFromUrl(string $url): string
    {
        if (Regexp::match('/#(?<profile>.+)$/', $url, $matches)) {
            return $matches['profile'];
        } else {
            return '';
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
