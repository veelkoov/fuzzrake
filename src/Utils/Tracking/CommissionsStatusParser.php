<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Entity\ArtisanCommissionsStatus;
use App\Utils\Web\Snapshot\WebpageSnapshot;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\PatternInterface;

class CommissionsStatusParser
{
    /**
     * @var PatternInterface[]
     */
    private array $falsePositivePatterns;

    /**
     * @var PatternInterface[]
     */
    private array $offerStatusPatterns;

    public function __construct(
        private HtmlPreprocessor $htmlPreprocessor,
        private Patterns $patternFactory,
    ) {
        $this->falsePositivePatterns = $this->patternFactory->getFalsePositivePatterns();
        $this->offerStatusPatterns = $this->patternFactory->getOfferStatusPatterns();
    }

    /**
     * @return ArtisanCommissionsStatus[]
     *
     * @throws TrackerException
     */
    public function getCommissionsStatuses(WebpageSnapshot $snapshot): array
    {
        $additionalFilter = HtmlPreprocessor::guessFilterFromUrl($snapshot->getUrl());
        $artisanName = $snapshot->getOwnerName();

        $inputTexts = array_map(fn (string $input) => $this->preprocess($input, $artisanName, $additionalFilter), $snapshot->getAllContents());

        $result = [];

        foreach ($inputTexts as $inputText) {
            foreach ($this->getStatusesFromString($inputText) as $offer => $status) {
                $result[] = (new ArtisanCommissionsStatus())
                    ->setOffer($offer)
                    ->setIsOpen($status);
            }
        }

        return $result;
    }

    /**
     * @throws TrackerException
     */
    private function preprocess(string $inputText, string $artisanName, string $additionalFilter): string
    {
        $inputText = $this->htmlPreprocessor->clean($inputText);
        $inputText = HtmlPreprocessor::processArtisansName($artisanName, $inputText);
        $inputText = $this->removeFalsePositives($inputText);
        $inputText = HtmlPreprocessor::applyFilters($inputText, $additionalFilter);

        return $inputText;
    }

    private function removeFalsePositives(string $contents): string
    {
        foreach ($this->falsePositivePatterns as $pattern) {
            $contents = $pattern->remove($contents);
        }

        return $contents;
    }

    /**
     * @return bool[] (string)offer => (bool)status
     *
     * @throws TrackerException
     */
    private function getStatusesFromString(string $inputText): array
    {
        $result = [];

        foreach ($this->offerStatusPatterns as $statusPattern) {
            $statusPattern->match($inputText)->forEach(function (Detail $match) use (&$result): void {
                [$offer, $status] = $this->patternFactory->matchStatusAndOfferFrom($match);

                if (array_key_exists($offer, $result)) {
                    $status = false; // TODO: Better handling, use NULL for arrrgh
                }

                $result[$offer] = $status;
            });
        }

        return $result;
    }
}
