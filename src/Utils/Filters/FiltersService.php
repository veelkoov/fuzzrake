<?php

declare(strict_types=1);

namespace App\Utils\Filters;

use App\Repository\ArtisanCommissionsStatusRepository;
use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Service\CountriesDataService;
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
            $this->artisanRepository->getDistinctLanguages(),
            $this->getCountriesFilterData(),
            $this->artisanRepository->getDistinctStatesToCountAssoc(),
            $this->getSpeciesFilterItems(),
        );
    }

    private function getCountriesFilterData(): FilterData
    {
        $artisansCountries = $this->artisanRepository->getDistinctCountriesToCountAssoc();

        $unknown = SpecialItems::newUnknown($artisansCountries->getSpecialItems()[0]->getCount()); // FIXME: Refactor filters/stats #80 - ugly hack [0]
        $result = new FilterData($unknown);

        foreach ($this->countriesDataService->getRegions() as $regionName) {
            $result->getItems()->addComplexItem($regionName, new Set(), $regionName, 0);
        }

        foreach ($artisansCountries->getItems() as $country) {
            $code = $country->getValueString();
            $region = $this->countriesDataService->getRegionFrom($code);
            $name = $this->countriesDataService->getNameFor($code);

            $result->getItems()[$region]->incCount($country->getCount());
            $result->getItems()[$region]->getValueSet()->addComplexItem($code, $code, $name, $country->getCount());
        }

        foreach ($result->getItems() as $item) {
            $item->getValueSet()->sort();
        }

        return $result;
    }

    private function getSpeciesFilterItems(): Set
    {
        return $this->getSpeciesFilterItemsFromArray($this->species->getTree());
    }

    /**
     * @param Specie[] $species
     */
    private function getSpeciesFilterItemsFromArray(array $species): Set
    {
        $result = new Set();

        foreach ($species as $specie) {
            if (!$specie->isIgnored()) {
                $result->addComplexItem($specie->getName(), $this->getSpeciesFilterItem($specie), $specie->getName(), 0); // TODO: #76 Species count
            }
        }

        return $result;
    }

    private function getSpeciesFilterItem(Specie $specie): Set|string
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
    private function getCommissionsStatuses(): FilterData
    {
        $trackedCount = $this->artisanRepository->getCsTrackedCount();
        $issuesCount = $this->artisanVolatileDataRepository->getCsTrackingIssuesCount();
        $activeCount = $this->artisanRepository->countActive();
        $nonTrackedCount = $activeCount - $trackedCount;

        $trackingIssues = SpecialItems::newTrackingIssues($issuesCount);
        $notTracked = SpecialItems::newNotTracked($nonTrackedCount);
        $result = new FilterData($trackingIssues, $notTracked);

        foreach ($this->artisanCommissionsStatusRepository->getDistinctWithOpenCount() as $offer => $openCount) {
            $result->getItems()->addComplexItem($offer, $offer, $offer, (int) $openCount);
        }

        return $result;
    }

    private function getPaymentPlans(): FilterData
    {
        $unknown = SpecialItems::newUnknown();
        $result = new FilterData($unknown);

        foreach ($this->artisanRepository->getPaymentPlans() as $paymentPlan) {
            if ('' === $paymentPlan) {
                $unknown->incCount();
            } elseif ('None' === $paymentPlan) { // grep-payment-plans-none
                $result->getItems()->addOrIncItem('Not supported'); // grep-payment-plans-none-label
            } else {
                $result->getItems()->addOrIncItem('Supported'); // grep-payment-plans-any-label
            }
        }

        return $result;
    }
}
