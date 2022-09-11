<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

class Regexes
{
    /**
     * @param string[]                $falsePositives
     * @param string[]                $offerStatuses
     * @param array<string, string[]> $groupTranslations
     * @param string[]                $cleaners
     */
    public function __construct(
        private readonly array $falsePositives,
        private readonly array $offerStatuses,
        private readonly array $groupTranslations,
        private readonly array $cleaners,
    ) {
    }

    /**
     * @return string[]
     */
    public function getFalsePositives(): array
    {
        return $this->falsePositives;
    }

    /**
     * @return string[]
     */
    public function getOfferStatuses(): array
    {
        return $this->offerStatuses;
    }

    /**
     * @return array<string, string[]>
     */
    public function getGroupTranslations(): array
    {
        return $this->groupTranslations;
    }

    /**
     * @return string[]
     */
    public function getCleaners(): array
    {
        return $this->cleaners;
    }
}
