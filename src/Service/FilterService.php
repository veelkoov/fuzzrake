<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Utils\FilterItems;
use App\Utils\Species\Specie;
use App\Utils\Species\Species;
use Doctrine\ORM\UnexpectedResultException;

class FilterService
{
    public function __construct(
        private ArtisanRepository $artisanRepository,
        private ArtisanCommissionsStatusRepository $artisanCommissionsStatusRepository,
        private ArtisanVolatileDataRepository $artisanVolatileDataRepository,
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

    /**
     * @throws UnexpectedResultException
     */
    private function getCommissionsStatuses(): FilterItems
    {
        $result = new FilterItems(false, false);

        $trackedCount = $this->artisanRepository->getCsTrackedCount();
        $issuesCount = $this->artisanVolatileDataRepository->getCsTrackingIssuesCount();
        $activeCount = $this->artisanRepository->countActive();
        $nonTrackedCount = $activeCount - $trackedCount;

        foreach ($this->artisanCommissionsStatusRepository->getDistinctWithOpenCount() as $offer => $openCount) {
            $result->addComplexItem('commissionsStatus', $offer, $offer, (int) $openCount);
        }

        $result->addComplexItem('commissionsStatus', '?', 'Tracking issues', $issuesCount);
        $result->addComplexItem('commissionsStatus', '-', 'Not tracked', $nonTrackedCount);

        return $result;
    }
}
