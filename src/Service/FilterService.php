<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanRepository;
use App\Utils\Data\Definitions\Species;
use App\Utils\FilterItems;

class FilterService
{
    private ArtisanRepository $artisanRepository;
    private CountriesDataService $countriesDataService;
    private Species $species;

    public function __construct(ArtisanRepository $artisanRepository, CountriesDataService $countriesDataService, Species $species)
    {
        $this->artisanRepository = $artisanRepository;
        $this->countriesDataService = $countriesDataService;
        $this->species = $species;
    }

    public function getFiltersTplData()
    {
        return [
            'orderTypes'          => $this->artisanRepository->getDistinctOrderTypes(),
            'styles'              => $this->artisanRepository->getDistinctStyles(),
            'features'            => $this->artisanRepository->getDistinctFeatures(),
            'productionModels'    => $this->artisanRepository->getDistinctProductionModels(),
            'commissionsStatuses' => $this->artisanRepository->getDistinctCommissionStatuses(),
            'languages'           => $this->artisanRepository->getDistinctLanguages(),
            'countries'           => $this->countriesDataService->getFilterData(),
            'states'              => $this->artisanRepository->getDistinctStatesToCountAssoc(),
            'species'             => $this->getSpeciesFilterItems(),
        ];
    }

    private function getSpeciesFilterItems(): FilterItems
    {
        return $this->getSpeciesFilterItemsFromArray($this->species->getFilterChoicesTree());
    }

    private function getSpeciesFilterItemsFromArray($species): FilterItems
    {
        $result = new FilterItems(true);

        foreach ($species as $specie => $subspecies) {
            $result->addComplexItem($specie, $this->getSpeciesFilterItem($specie, $subspecies), $specie, 0); // TODO: count
        }

        return $result;
    }

    /**
     * @return FilterItems|string
     */
    private function getSpeciesFilterItem(string $specie, array $subspecies)
    {
        if (!empty($subspecies)) {
            return $this->getSpeciesFilterItemsFromArray($subspecies);
        } else {
            return $specie;
        }
    }
}
