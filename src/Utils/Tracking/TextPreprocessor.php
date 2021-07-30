<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Json;
use App\Utils\Regexp\Replacements;
use App\Utils\UnbelievableRuntimeException;
use App\Utils\Web\WebsiteInfo;
use JsonException;
use Symfony\Component\DomCrawler\Crawler;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\PatternInterface;

class TextPreprocessor
{
    private const REPLACEMENTS = [
        '(?<=function|try|if|catch|else[;,{})]) (?=function|catch|else[{}\$(])' => '_',
        '(?<=return|delete) (?=this)'                                           => '_',
        '<script[^>]*>[^ ]+</script>'                                           => ' ',
        '<meta property="(og:)?description"[^>]+>'                              => ' ', // Seems to duplicate primary content

        '&nbsp;'                                          => ' ',
        "\u{00A0}"                                        => ' ', // NBSP
        '<style[^>]*>.*?</style>'                         => ' ',
        '<!--.*?-->'                                      => ' ',
        '</?(?:strong|b|i|span|center|u|a|em|font)[^>]*>' => '',
        '\*\*\*OPEN\*\*\*'                                => 'open',

        '  +'   => ' ',
        "\n\n+" => "\n",
    ];

    private Replacements $replacements;

    /**
     * @param PatternInterface[] $falsePositivePatterns
     */
    public function __construct(
        private array $falsePositivePatterns,
    ) {
        $this->replacements = new Replacements(self::REPLACEMENTS, 's', '', '');
    }

    /**
     * @throws TrackerException
     */
    public function getText(string $inputText, string $artisanName, string $additionalFilter): Text
    {
        $contents = $this->extractFromJson($inputText);
        $contents = strtolower($contents);
        $contents = $this->applyReplacements($contents);
        $contents = self::replaceArtisanName($artisanName, $contents);
        $contents = $this->removeFalsePositives($contents);
        $contents = $this->applyFilters($contents, $additionalFilter); // TODO: Should take place at the beginning maybe?

        return new Text($inputText, $contents);
    }

    public static function guessFilterFromUrl(string $url): string
    {
        try {
            return pattern('#(?<profile>.+)$')->match($url)
                ->findFirst(fn (Detail $match): string => $match->group('profile')->text())
                ->orReturn('');
        } catch (NonexistentGroupException $e) {
            throw new UnbelievableRuntimeException($e);
        }
    }

    public static function replaceArtisanName(string $artisanName, string $inputText): string
    {
        $inputText = str_ireplace($artisanName, 'STUDIO_NAME', $inputText);

        if (strlen($artisanName) > 2 && 's' === strtolower(substr($artisanName, -1))) {
            /* Thank you, English language, I am enjoying this */
            $inputText = str_ireplace(substr($artisanName, 0, -1)."'s", 'STUDIO_NAME', $inputText);
        }

        return $inputText;
    }

    private function extractFromJson(string $webpage): string
    {
        if (empty($webpage) || '{' !== $webpage[0]) {
            return $webpage;
        }

        try {
            $result = Json::decode($webpage);
        } catch (JsonException) {
            return $webpage;
        }

        return $this->flattenArray($result);
    }

    /**
     * https://stackoverflow.com/questions/1319903/how-to-flatten-a-multidimensional-array#comment7768057_1320156.
     */
    private function flattenArray(array $array): string
    {
        $result = '';

        array_walk_recursive($array, function ($a, $b) use (&$result) {
            $result .= "$b: $a\n";
        });

        return $result;
    }

    /**
     * @throws TrackerException
     * @noinspection PhpUnusedParameterInspection TODO: Better handling of FA statuses #81
     */
    private function applyFilters(string $inputText, string $additionalFilter): string
    {
        if (WebsiteInfo::isFurAffinity(null, $inputText)) {
            if (WebsiteInfo::isFurAffinityUserProfile(null, $inputText)) {
                // $additionalFilter = 'profile' === $additionalFilter ? 'td[width="80%"][align="left"]' : '';
                $additionalFilter = ''; // TODO: Better handling of FA statuses #81

                $crawler = new Crawler($inputText);
                $filtered = $crawler->filter('#page-userpage div.userpage-profile '.$additionalFilter);

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

    private function removeFalsePositives(string $contents): string
    {
        foreach ($this->falsePositivePatterns as $pattern) {
            $contents = $pattern->remove($contents)->all();
        }

        return $contents;
    }

    private function applyReplacements(string $contents): string
    {
        return $this->replacements->do($contents);
    }
}
