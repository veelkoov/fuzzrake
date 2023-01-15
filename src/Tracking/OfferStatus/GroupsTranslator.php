<?php

declare(strict_types=1);

namespace App\Tracking\OfferStatus;

readonly class GroupsTranslator
{
    /**
     * @param array<string, list<string>> $groupsTranslations Key = captured group name, value = list of offers or status applied if group matched
     */
    public function __construct(
        private array $groupsTranslations,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getOffersOrStatus(string $capturedGroupName): array
    {
        return $this->groupsTranslations[$capturedGroupName] ?? []; // TODO: Shouldn't we throw instead of [] ?
    }
}
