<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Exception\TrackerException;
use App\Utils\Json;
use JsonException;
use Nette\Utils\Arrays;
use Symfony\Component\DomCrawler\Crawler;
use TRegx\CleanRegex\Pattern;

readonly class TextPreprocessor
{
    private Pattern $instaBio;

    public function __construct(
    ) {
        // TODO: Naive (fixed formatting, bio without "), possibly find a better place
        $this->instaBio = Pattern::of('"graphql":\{"user":\{"biography":(?<bio>"[^"]+")');
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

        if ($this->detector->isInstagram($url)) {
            $match = $this->instaBio->match($inputText);

            if ($match->test()) {
                $unparsed = $parsed = $match->first()->get('bio');

                try {
                    $parsed = Json::decode($unparsed);
                } catch (JsonException) {
                }

                return is_string($parsed) ? $parsed : $unparsed;
            }
        }

        return $inputText;
    }
}
