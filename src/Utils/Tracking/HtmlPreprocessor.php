<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Regexp\RegexpFailure;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\Web\WebsiteInfo;
use Symfony\Component\DomCrawler\Crawler;

class HtmlPreprocessor
{
    public static function processArtisansName(string $artisanName, string $inputText)
    {
        $inputText = str_ireplace($artisanName, 'STUDIO_NAME', $inputText);
        if (strlen($artisanName) > 2 && 's' === strtolower(substr($artisanName, -1))) {
            /* Thank you, English language, I am enjoying this */
            $inputText = str_ireplace(substr($artisanName, 0, -1)."'s", 'STUDIO_NAME', $inputText);
        }

        return $inputText;
    }

    /**
     * @param string $inputText
     *
     * @return string
     *
     * @throws RegexpFailure
     */
    public static function cleanHtml(string $inputText): string
    {
        $inputText = strtolower($inputText);
        $inputText = HtmlPreprocessor::extractFromJson($inputText);

        foreach (CommissionsStatusRegexps::HTML_CLEANER_REGEXPS as $regexp => $replacement) {
            $inputText = Regexp::replace($regexp, $replacement, $inputText);
        }

        return $inputText;
    }

    private static function extractFromJson(string $webpage)
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
     *
     * @param array $array
     *
     * @return string
     */
    private static function flattenArray(array $array)
    {
        $result = '';

        array_walk_recursive($array, function ($a, $b) use (&$result) {
            $result .= "$b: $a\n";
        });

        return $result;
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @throws RegexpFailure
     */
    public static function guessFilterFromUrl(string $url): string
    {
        if (Regexp::match('/#(?<profile>.+)$/', $url, $matches)) {
            return $matches['profile'];
        } else {
            return '';
        }
    }

    /**
     * @param string $inputText
     * @param string $additionalFilter
     *
     * @return string
     *
     * @throws TrackerException
     * @throws RegexpFailure
     */
    public static function applyFilters(string $inputText, string $additionalFilter): string
    {
        if (WebsiteInfo::isFurAffinity(null, $inputText)) {
            if (false !== stripos($inputText, '<p class="link-override">The owner of this page has elected to make it available to registered users only.')) {
                throw new TrackerException('FurAffinity login required');
            }

            if (WebsiteInfo::isFurAffinityUserProfile(null, $inputText)) {
                $additionalFilter = 'profile' === $additionalFilter ? 'td[width="80%"][align="left"]' : '';

                $crawler = new Crawler($inputText);

                return $crawler->filter('#page-userpage tr:first-child table.maintable '.$additionalFilter)->html();
            }

            return $inputText;
        }

        if (WebsiteInfo::isTwitter($inputText)) {
            $crawler = new Crawler($inputText);

            return $crawler->filter('div.profileheadercard')->html();
        }

        if (WebsiteInfo::isInstagram($inputText)) {
            $crawler = new Crawler($inputText);

            return $crawler->filter('script[type="application/ld+json"]')->html();
        }

        return $inputText;
    }
}
