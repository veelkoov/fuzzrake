<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests\Filters;

use App\Data\Species\SpeciesService;

class SpeciesFilterFactory
{
    public function __construct(
        private readonly SpeciesService $speciesSrv,
    ) {
    }

    /**
     * @param list<string> $wantedItems
     */
    public function get(array $wantedItems): SpeciesFilter
    {
        return new SpeciesFilter($wantedItems, $this->speciesSrv->getSpecies()->list);
    }
}
