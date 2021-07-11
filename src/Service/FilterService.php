<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Repository\ArtisanRepository;
use App\Utils\FilterItems;
use App\Utils\Species\Specie;
use App\Utils\Species\Species;

class FilterService
{
    public function __construct(
        private ArtisanRepository $artisanRepository,
        private ArtisanCommissionsStatusRepository $artisanCommissionsStatusRepository,
        private CountriesDataService $countriesDataService,
        private Species $species,
    ) {
    }

    public function getFiltersTplData(): array
    {
        return [
            'orderTypes'          => $this->artisanRepository->getDistinctOrderTypes(),
            'styles'              => $this->artisanRepository->getDistinctStyles(),
            'features'            => $this->artisanRepository->getDistinctFeatures(),
            'productionModels'    => $this->artisanRepository->getDistinctProductionModels(),
            'commissionsStatuses' => $this->getCommissionsStatuses(),
            'languages'           => $this->artisanRepository->getDistinctLanguages(),
            'countries'           => $this->countriesDataService->getFilterData(),
            'states'              => $this->artisanRepository->getDistinctStatesToCountAssoc(),
            'species'             => $this->getSpeciesFilterItems(),
        ];
    }

    private function getSpeciesFilterItems(): FilterItems
    {
        return $this->getSpeciesFilterItemsFromArray($this->species->getSpeciesTree());
    }

    /**
     * @param Specie[] $species
     */
    private function getSpeciesFilterItemsFromArray(array $species): FilterItems
    {
        $result = new FilterItems(true);

        foreach ($species as $specie) {
            $result->addComplexItem($specie->getName(), $this->getSpeciesFilterItem($specie), $specie->getName(), 0); // TODO: count
        }

        return $result;
    }

    private function getSpeciesFilterItem(Specie $specie): FilterItems | string
    {
        if ($specie->hasChildren()) {
            return $this->getSpeciesFilterItemsFromArray($specie->getChildren());
        } else {
            return $specie->getName();
        }
    }

    private function getCommissionsStatuses(): FilterItems
    {
        $result = new FilterItems(false);

        foreach ($this->artisanCommissionsStatusRepository->getDistinctWithOpenCount() as $offer => $openCount) {
            $result->addComplexItem($offer, $offer, $offer, (int) $openCount);
        }

        return $result;
    }
}
