<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\Web\Snapshot\WebpageSnapshot;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class CommissionsStatusParser
{
    /**
     * @var Pattern[]
     */
    private array $offerStatusPatterns;

    private TextPreprocessor $preprocessor;

    public function __construct(
        private Patterns $patterns,
    ) {
        $this->offerStatusPatterns = $this->patterns->getOfferStatusPatterns();
        $this->preprocessor = new TextPreprocessor($this->patterns->getFalsePositivePatterns());
    }

    /**
     * @return OfferStatus[]
     *
     * @throws TrackerException
     */
    public function getCommissionsStatuses(WebpageSnapshot $snapshot): array
    {
        $additionalFilter = TextPreprocessor::guessFilterFromUrl($snapshot->getUrl());
        $artisanName = $snapshot->getOwnerName();

        $texts = $this->preprocessAll($artisanName, $additionalFilter, $snapshot);

        $result = [];

        foreach ($texts as $text) {
            array_push($result, ...$this->getOfferStatusesFrom($text));
        }

        return $result;
    }

    /**
     * @throws TrackerException
     */
    private function preprocessAll(string $artisanName, string $additionalFilter, WebpageSnapshot $snapshot): array
    {
        return array_map(fn (string $input): Text => $this->preprocessor->getText($input, $artisanName, $additionalFilter), $snapshot->getAllContents());
    }

    /**
     * @return OfferStatus[]
     *
     * @throws TrackerException
     */
    private function getOfferStatusesFrom(Text $text): array
    {
        $result = [];

        foreach ($this->offerStatusPatterns as $statusPattern) {
            $statusPattern->match($text->getUnused())->forEach(function (Detail $match) use (&$result, $text): void {
                $text->use($match->byteOffset(), $match->byteTail());

                array_push($result, ...$this->patterns->matchStatusAndOfferFrom($match));
            });
        }

        return $result;
    }
}
