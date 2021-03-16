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
    private array $offerStatusPatterns;

    private TextPreprocessor $preprocessor;

    public function __construct(
        private Patterns $patterns,
    ) {
        $this->offerStatusPatterns = $this->patterns->getOfferStatusPatterns();
        $this->preprocessor = new TextPreprocessor($this->patterns->getFalsePositivePatterns());
    }

    /**
     * @return ArtisanCommissionsStatus[]
     *
     * @throws TrackerException
     */
    public function getCommissionsStatuses(WebpageSnapshot $snapshot): array
    {
        $additionalFilter = TextPreprocessor::guessFilterFromUrl($snapshot->getUrl());
        $artisanName = $snapshot->getOwnerName();

        $texts = array_map(fn (string $input): Text => $this->preprocess($input, $artisanName, $additionalFilter), $snapshot->getAllContents());

        $result = [];

        foreach ($texts as $text) {
            foreach ($this->getStatusesFromString($text) as $offer => $status) {
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
    private function preprocess(string $inputText, string $artisanName, string $additionalFilter): Text
    {
        return $this->preprocessor->getText($inputText, $artisanName, $additionalFilter);
    }

    /**
     * @return bool[] (string)offer => (bool)status
     *
     * @throws TrackerException
     */
    private function getStatusesFromString(Text $text): array
    {
        $result = [];

        foreach ($this->offerStatusPatterns as $statusPattern) {
            $statusPattern->match($text->get())->forEach(function (Detail $match) use (&$result): void {
                [$offer, $status] = $this->patterns->matchStatusAndOfferFrom($match);

                if (array_key_exists($offer, $result)) {
                    $status = false; // TODO: Better handling, use NULL for arrrgh
                }

                $result[$offer] = $status;
            });
        }

        return $result;
    }
}
