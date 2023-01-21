<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\DataRequests\Consts;
use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\MutableSet;
use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Repository\ArtisanCommissionsStatusRepository;
use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Service\CountriesDataService;
use App\Utils\Enforce;
use App\Utils\Species\Specie;
use App\Utils\Species\SpeciesService;
use Doctrine\ORM\UnexpectedResultException;

class FiltersService
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
        private readonly ArtisanCommissionsStatusRepository $artisanCommissionsStatusRepository,
        private readonly ArtisanVolatileDataRepository $artisanVolatileDataRepository,
        private readonly CountriesDataService $countriesDataService,
        private readonly SpeciesService $species,
    ) {
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getFiltersTplData(): FiltersData
    {
        return new FiltersData(
            $this->artisanRepository->getDistinctOrderTypes(),
            $this->artisanRepository->getDistinctStyles(),
            $this->getPaymentPlans(),
            $this->artisanRepository->getDistinctFeatures(),
            $this->artisanRepository->getDistinctProductionModels(),
            $this->getCommissionsStatuses(),
            $this->artisanRepository->getDistinctLanguagesForFilters(),
            $this->getCountriesFilterData(),
            $this->artisanRepository->getDistinctStatesToCountAssoc(),
            $this->getSpeciesFilterItems(),
        );
    }

    private function getCountriesFilterData(): FilterData
    {
        $artisansCountries = $this->artisanRepository->getDistinctCountriesToCountAssoc();

        $unknown = SpecialItems::newUnknown($artisansCountries->specialItems[0]->count); // FIXME: Refactor filters/stats #80 - ugly hack [0]
        $result = new MutableFilterData($unknown);

        foreach ($this->countriesDataService->getRegions() as $regionName) {
            $result->items->addComplexItem($regionName, new MutableSet(), $regionName, 0);
        }

        foreach ($artisansCountries->items as $country) {
            $code = Enforce::string($country->value);
            $region = $this->countriesDataService->getRegionFrom($code);
            $name = $this->countriesDataService->getNameFor($code);

            $result->items[$region]->incCount($country->count);
            $result->items[$region]->getValueSet()->addComplexItem($code, $code, $name, $country->count);
        }

        foreach ($result->items as $item) {
            $item->getValueSet()->sort();
        }

        return new FilterData($result);
    }

    /**
     * @return list<Item>
     */
    private function getSpeciesFilterItems(): array
    {
        return $this->getSpeciesFilterItemsFromArray($this->species->getVisibleTree())->getReadonlyList();
    }

    /**
     * @param list<Specie> $species
     */
    private function getSpeciesFilterItemsFromArray(array $species): MutableSet
    {
        $result = new MutableSet();

        foreach ($species as $specie) {
            if (!$specie->isHidden()) {
                $result->addComplexItem($specie->getName(), $this->getSpeciesFilterItem($specie), $specie->getName(), 0); // TODO: #76 Species count
            }
        }

        return $result;
    }

    private function getSpeciesFilterItem(Specie $specie): MutableSet|string
    {
        if ($specie->isLeaf()) {
            return $specie->getName();
        } else {
            return $this->getSpeciesFilterItemsFromArray($specie->getChildren());
        }
    }

    /**
     * @throws UnexpectedResultException
     */
    private function getCommissionsStatuses(): FilterData
    {
        $trackedCount = $this->artisanRepository->getCsTrackedCount();
        $issuesCount = $this->artisanVolatileDataRepository->getCsTrackingIssuesCount();
        $activeCount = $this->artisanRepository->countActive();
        $nonTrackedCount = $activeCount - $trackedCount;

        $trackingIssues = SpecialItems::newTrackingIssues($issuesCount);
        $notTracked = SpecialItems::newNotTracked($nonTrackedCount);
        $result = new MutableFilterData($trackingIssues, $notTracked);

        foreach ($this->artisanCommissionsStatusRepository->getDistinctWithOpenCount() as $offer => $openCount) {
            $result->items->addComplexItem($offer, $offer, $offer, (int) $openCount);
        }

        return new FilterData($result);
    }

    private function getPaymentPlans(): FilterData
    {
        $unknown = SpecialItems::newUnknown();
        $result = new MutableFilterData($unknown);

        foreach ($this->artisanRepository->getPaymentPlans() as $paymentPlan) {
            if (Consts::DATA_VALUE_UNKNOWN === $paymentPlan) {
                $unknown->incCount();
            } elseif (Consts::DATA_PAYPLANS_NONE === $paymentPlan) {
                $result->items->addOrIncItem(Consts::FILTER_VALUE_PAYPLANS_NONE);
            } else {
                $result->items->addOrIncItem(Consts::FILTER_VALUE_PAYPLANS_SUPPORTED);
            }
        }

        return new FilterData($result);
    }
}
