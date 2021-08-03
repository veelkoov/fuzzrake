<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

use App\Utils\UnbelievableRuntimeException;
use TRegx\CleanRegex\Exception\NonexistentGroupException;
use TRegx\CleanRegex\Match\Details\Detail;
use TRegx\CleanRegex\Pattern;

class Patterns
{
    /**
     * @var Pattern[]
     */
    private array $falsePositivePatterns;

    /**
     * @var Pattern[]
     */
    private array $offerStatusPatterns;

    private Matcher $statusMatcher;

    private Matcher $offerMatcher;

    public function __construct()
    {
        $factory = new PatternsFactory(Regexes::COMMON_REGEXES, Regexes::OFFER_REGEXES, Regexes::STATUS_REGEXES);

        $this->falsePositivePatterns = $factory->generateFrom(Regexes::FALSE_POSITIVES_REGEXES);
        $this->offerStatusPatterns = $factory->generateFrom(Regexes::OFFER_STATUS_REGEXES);
        $this->statusMatcher = new Matcher($factory->generateFrom(Regexes::STATUS_REGEXES));
        $this->offerMatcher = new Matcher($factory->generateFrom(Regexes::OFFER_REGEXES));
    }

    /**
     * @return Pattern[]
     */
    public function getFalsePositivePatterns(): array
    {
        return $this->falsePositivePatterns;
    }

    /**
     * @return Pattern[]
     */
    public function getOfferStatusPatterns(): array
    {
        return $this->offerStatusPatterns;
    }

    /**
     * @return OfferStatus[]
     *
     * @throws TrackerException
     */
    public function matchStatusAndOfferFrom(Detail $match): array
    {
        try {
            $offer = $match->get(Regexes::GRP_OFFER);
            $status = $match->get(Regexes::GRP_STATUS);

            $offer = $this->offerMatcher->getKeyOfPatternMatching($offer);
            $status = Regexes::KEY_OPEN === $this->statusMatcher->getKeyOfPatternMatching($status);

            return array_map(fn ($item) => new OfferStatus($item, $status), explode('&', $offer));
        } catch (NonexistentGroupException $e) {
            throw new UnbelievableRuntimeException($e);
        }
    }
}
