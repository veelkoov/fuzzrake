<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Exception\TrackerException;
use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\Regex\PatternProvider;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

use function Psl\Vec\map;

class TextParser
{
    /**
     * @var Pattern[]
     */
    private readonly array $offerStatusPatterns;

    /**
     * @var string[][]
     */
    private readonly array $groupTranslations;

    private readonly TextPreprocessor $preprocessor;

    public function __construct(
        PatternProvider $provider,
    ) {
        $this->offerStatusPatterns = $provider->getOfferStatuses();
        $this->preprocessor = new TextPreprocessor($provider->getFalsePositives(), $provider->getCleaners());
        $this->groupTranslations = $provider->getGroupTranslations();
    }

    /**
     * @return OfferStatus[]
     *
     * @throws TrackerException
     */
    public function getOfferStatuses(Snapshot $snapshot): array
    {
        $texts = $this->getPreprocessedTexts($snapshot);

        $result = [];

        foreach ($texts as $text) {
            array_push($result, ...$this->getOfferStatusesFrom($text));
        }

        return $result;
    }

    /**
     * @return Text[]
     *
     * @throws TrackerException
     */
    private function getPreprocessedTexts(Snapshot $snapshot): array
    {
        return map($snapshot->getAllContents(), fn (string $input): Text => $this->preprocessor->getText($input, $snapshot->url, $snapshot->ownerName));
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

                $this->appendOfferStatuses($match, $result);
            });
        }

        return $result;
    }

    /**
     * @param OfferStatus[] $result
     *
     * @throws TrackerException
     */
    private function appendOfferStatuses(Detail $match, array &$result): void
    {
        $status = null;
        $offers = [];

        try {
            $nonEmptyGroups = array_filter($match->namedGroups()->names(), fn ($name) => $match->matched($name));
        } catch (NonexistentGroupException $e) {
            throw new UnbelievableRuntimeException($e);
        }

        $detail = "{$match->text()} (groups: ".implode(', ', $nonEmptyGroups).')';

        foreach ($nonEmptyGroups as $groupName) {
            foreach ($this->groupTranslations[$groupName] as $translation) {
                if (str_starts_with($translation, 'STATUS:')) { // grep-offer-status-constants
                    if (null !== $status) {
                        throw new TrackerException("Double status caught in: $detail");
                    }

                    $status = 'STATUS:OPEN' === $translation; // grep-offer-status-constants
                } else {
                    $offers[] = $translation;
                }
            }
        }

        if (null === $status) {
            throw new TrackerException("Missing status group in: $detail");
        }

        if ([] === $offers) {
            throw new TrackerException("Missing offer group(s) in: $detail");
        }

        foreach ($offers as $offer) {
            $result[] = new OfferStatus($offer, $status);
        }
    }
}
