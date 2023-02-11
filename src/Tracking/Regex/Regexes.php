<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

readonly class Regexes
{
    public const FALSE_POSITIVES_FLAGS = 'sn';
    public const OFFER_STATUSES_FLAGS = 'sn';
    public const CLEANERS_FLAGS = 's';

    /**
     * @param list<string>          $falsePositives
     * @param list<string>          $offerStatuses
     * @param array<string, string> $cleaners
     */
    public function __construct(
        public array $falsePositives,
        public array $offerStatuses,
        public array $cleaners,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getFalsePositives(): array
    {
        return $this->falsePositives;
    }

    /**
     * @return list<string>
     */
    public function getOfferStatuses(): array
    {
        return $this->offerStatuses;
    }

    /**
     * @return array<string, string>
     */
    public function getCleaners(): array
    {
        return $this->cleaners;
    }
}
