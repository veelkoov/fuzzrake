<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Tracking\Exception\TrackerException;
use App\Tracking\OfferStatus\GroupsTranslator;
use App\Tracking\OfferStatus\OfferStatus;
use App\Tracking\Regex\PatternProvider;
use App\Tracking\Web\WebpageSnapshot\Snapshot;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Match\Group;
use TRegx\CleanRegex\Pattern;

use function Psl\Vec\map;

class TextParser
{
    /**
     * @var list<Pattern>
     */
    private readonly array $offerStatusPatterns;

    private readonly GroupsTranslator $translator;
    private readonly TextPreprocessor $preprocessor;

    public function __construct(
        PatternProvider $provider,
    ) {
        $this->offerStatusPatterns = $provider->getOfferStatuses();
        $this->preprocessor = new TextPreprocessor($provider->getFalsePositives(), $provider->getCleaners());
        $this->translator = $provider->getGroupsTranslator();
    }

    /**
     * @return list<OfferStatus>
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
     * @return list<Text>
     *
     * @throws TrackerException
     */
    private function getPreprocessedTexts(Snapshot $snapshot): array
    {
        return map($snapshot->getAllContents(), fn (string $input): Text => $this->preprocessor->getText($input, $snapshot->url, $snapshot->ownerName));
    }

    /**
     * @return list<OfferStatus>
     *
     * @throws TrackerException
     */
    private function getOfferStatusesFrom(Text $text): array
    {
        $result = [];

        foreach ($this->offerStatusPatterns as $statusPattern) {
            $statusPattern->match($text->getUnused())->forEach(function (Detail $match) use (&$result, $text): void {
                $text->use($match->byteOffset(), $match->byteTail());

                $this->appendDetectedOfferStatuses($match, $result);
            });
        }

        return $result;
    }

    /**
     * @param list<OfferStatus> $result
     *
     * @throws TrackerException
     */
    private function appendDetectedOfferStatuses(Detail $match, array &$result): void
    {
        $status = null;
        $offers = [];

        $matchedGroupNames = array_filter(map($match->namedGroups(), fn (Group $group) => $group->matched() ? $group->name() : null));

        $detail = "{$match->text()} (groups: ".implode(', ', $matchedGroupNames).')';

        foreach ($matchedGroupNames as $groupName) {
            foreach ($this->translator->getOffersOrStatus($groupName) as $translation) {
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
