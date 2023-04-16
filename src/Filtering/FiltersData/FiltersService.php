<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Data\Species\SpeciesService;
use App\Filtering\DataRequests\Consts;
use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\MutableSet;
use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Repository\ArtisanCommissionsStatusRepository;
use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Service\CountriesDataService;
use App\Utils\Enforce;
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
            $this->getOpenFor(),
            $this->artisanRepository->getDistinctLanguagesForFilters(),
            $this->getCountriesFilterData(),
            $this->artisanRepository->getDistinctStatesToCountAssoc(),
            $this->getSpeciesFilterData(),
            $this->getInactiveFilterData(),
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

    private function getSpeciesFilterData(): FilterData
    {
        return SpeciesFilterDataBuilder::for($this->species->getSpecies(), $this->species->getStats());
    }

    /**
     * @throws UnexpectedResultException
     */
    private function getOpenFor(): FilterData
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

    /**
     * @throws UnexpectedResultException
     */
    private function getInactiveFilterData(): FilterData
    {
        $inactiveCount = $this->artisanRepository->countAll() - $this->artisanRepository->countActive();

        return new FilterData(new MutableFilterData(SpecialItems::newInactive($inactiveCount)));
    }
}
