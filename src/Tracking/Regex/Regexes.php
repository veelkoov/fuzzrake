<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use App\Tracking\OfferStatus\GroupsTranslator;

readonly class Regexes
{
    /**
     * @param list<string>                $falsePositives
     * @param list<string>                $offerStatuses
     * @param array<string, list<string>> $groupsTranslations
     * @param array<string, string>       $cleaners
     */
    public function __construct(
        public array $falsePositives,
        public array $offerStatuses,
        public array $groupsTranslations,
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
     * @return array<string, list<string>>
     */
    public function getGroupTranslations(): array
    {
        return $this->groupsTranslations;
    }

    /**
     * @return array<string, string>
     */
    public function getCleaners(): array
    {
        return $this->cleaners;
    }

    public function getGroupsTranslator(): GroupsTranslator
    {
        return new GroupsTranslator($this->groupsTranslations);
    }
}
