<?php

declare(strict_types=1);

namespace App\Filtering\DataProvider\Filters;

use App\Utils\Species\SpeciesService;

class SpeciesFilterFactory
{
    public function __construct(
        private readonly SpeciesService $speciesService,
    ) {
    }

    /**
     * @param list<string> $wantedItems
     */
    public function get(array $wantedItems): SpeciesFilter
    {
        return new SpeciesFilter($wantedItems, new SpeciesSearchResolver($this->speciesService->getCompleteList()));
    }
}
