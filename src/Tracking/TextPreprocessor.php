<?php

/** @noinspection HtmlUnknownAttribute */

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Exception\TrackerException;
use App\Tracking\Web\WebsiteInfo;
use App\Utils\Json;
use App\Utils\Regexp\Replacements;
use App\Utils\UnbelievableRuntimeException;
use JsonException;
use Nette\Utils\Arrays;
use Symfony\Component\DomCrawler\Crawler;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class TextPreprocessor
{
    /**
     * @param Pattern[] $falsePositivePatterns
     */
    public function __construct(
        private readonly array $falsePositivePatterns,
        private readonly Replacements $replacements,
    ) {
    }

    /**
     * @throws TrackerException
     */
    public function getText(string $inputText, string $url, string $artisanName, string $additionalFilter): Text
    {
        $contents = $this->extractFromJson($inputText);
        $contents = strtolower($contents);
        $contents = $this->applyReplacements($contents);
        $contents = self::replaceArtisanName($artisanName, $contents);
        $contents = $this->removeFalsePositives($contents);
        $contents = $this->applyFilters($url, $contents, $additionalFilter);

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

        if (!is_array($result)) {
            $result = [$result];
        }

        return implode(' ', array_filter(Arrays::flatten($result), fn ($item): bool => is_string($item)));
    }

    /**
     * @throws TrackerException
     * @noinspection PhpUnusedParameterInspection TODO: Better handling of FA statuses #81
     */
    private function applyFilters(string $url, string $inputText, string $additionalFilter): string
    {
        if (WebsiteInfo::isFurAffinity($url, $inputText)) {
            if (WebsiteInfo::isFurAffinityUserProfile($url, $inputText)) {
                // $additionalFilter = 'profile' === $additionalFilter ? 'td[width="80%"][align="left"]' : '';
                $additionalFilter = ''; // TODO: Better handling of FA statuses #81

                $crawler = new Crawler($inputText);
                $filtered = $crawler->filter('#page-userpage div.userpage-profile '.$additionalFilter);

                if (1 !== $filtered->count()) {
                    if (str_contains($inputText, 'the owner of this page has elected to make it available to registered users only.')) {
                        throw new TrackerException('FA profile configured to allow logged-in users only.');
                    }

                    throw new TrackerException('Failed to filter FA profile, nodes count: '.$filtered->count());
                }

                return $filtered->html();
            }

            return $inputText;
        }

        if (WebsiteInfo::isTwitter($inputText)) {
            $crawler = new Crawler($inputText);
            $filtered = $crawler->filterXPath('//main//nav/preceding-sibling::div');

            if (0 === $filtered->count()) {
                $filtered = $crawler->filter('div.profileheadercard');
            }

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
            $contents = $pattern->prune($contents);
        }

        return $contents;
    }

    private function applyReplacements(string $contents): string
    {
        return $this->replacements->do($contents);
    }
}
