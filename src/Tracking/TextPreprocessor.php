<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Exception\TrackerException;
use App\Tracking\Web\Detector;
use App\Utils\Json;
use App\Utils\Regexp\Replacements;
use JsonException;
use Nette\Utils\Arrays;
use Symfony\Component\DomCrawler\Crawler;
use TRegx\CleanRegex\Pattern;
use TRegx\CleanRegex\PatternList;

class TextPreprocessor
{
    private readonly PatternList $falsePositivePatterns;
    private readonly Detector $detector;

    /**
     * @param Pattern[] $falsePositivePatterns
     */
    public function __construct(
        array $falsePositivePatterns,
        private readonly Replacements $replacements,
    ) {
        $this->falsePositivePatterns = Pattern::list($falsePositivePatterns);
        $this->detector = new Detector();
    }

    /**
     * @throws TrackerException
     */
    public function getText(string $inputText, string $url, string $artisanName): Text
    {
        $contents = $this->extractFromJson($inputText);
        $contents = strtolower($contents);
        $contents = $this->applyReplacements($contents);
        $contents = self::replaceArtisanName($artisanName, $contents);
        $contents = $this->falsePositivePatterns->prune($contents);
        $contents = $this->applyFilters($url, $contents);

        return new Text($inputText, $contents);
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
     */
    private function applyFilters(string $url, string $inputText): string
    {
        if ($this->detector->isFurAffinity($url)) {
            if ($this->detector->isNotFurAffinityJournal($inputText)) {
                $crawler = new Crawler($inputText);
                $filtered = $crawler->filter('#page-userpage div.userpage-profile');

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

        if ($this->detector->isTwitter($inputText)) {
            $crawler = new Crawler($inputText);
            $filtered = $crawler->filterXPath('//main//nav/preceding-sibling::div');

            if (1 !== $filtered->count()) {
                throw new TrackerException('Failed to filter Twitter profile, nodes count: '.$filtered->count());
            }

            return $filtered->html();
        }

        return $inputText;
    }

    private function applyReplacements(string $contents): string
    {
        return $this->replacements->do($contents);
    }
}
