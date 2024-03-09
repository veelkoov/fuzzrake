<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\DataRequests\Consts;
use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Repository\ArtisanRepository;
use App\Repository\ArtisanVolatileDataRepository;
use App\Repository\CreatorOfferStatusRepository;
use App\Repository\KotlinDataRepository;
use App\Service\CountriesDataService;
use App\Utils\Enforce;
use Doctrine\ORM\UnexpectedResultException;

class FiltersService
{
    public function __construct(
        private readonly ArtisanRepository $artisanRepository,
        private readonly CreatorOfferStatusRepository $offerStatusRepository,
        private readonly ArtisanVolatileDataRepository $artisanVolatileDataRepository,
        private readonly CountriesDataService $countriesDataService,
        private readonly KotlinDataRepository $kotlinDataRepository,
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
            $result->items->addComplexItem($regionName, $regionName, 0);
        }

        foreach ($artisansCountries->items as $country) {
            $code = Enforce::string($country->value);
            $region = $this->countriesDataService->getRegionFrom($code);
            $name = $this->countriesDataService->getNameFor($code);

            $result->items[$region]->incCount($country->count);
            $result->items[$region]->subitems->addComplexItem($code, $name, $country->count);
        }

        foreach ($result->items as $item) {
            $item->subitems->sort();
        }

        return FilterData::from($result);
    }

    private function getSpeciesFilterData(): FilterData
    {
        $rawFilterData = $this->kotlinDataRepository->getArray(KotlinDataRepository::SPECIES_FILTER);

        return $this->rawToFilterData($rawFilterData);
    }

    /**
     * @param array<mixed> $rawFilterData
     */
    private function rawToFilterData(array $rawFilterData): FilterData
    {
        $specialItems = [];

        foreach (Enforce::array($rawFilterData['specialItems'] ?? []) as $rawSpecialItem) {
            $rawSpecialItem = Enforce::array($rawSpecialItem);

            $specialItems[] = new SpecialItem(
                Enforce::string($rawSpecialItem['value'] ?? ''),
                Enforce::string($rawSpecialItem['label'] ?? ''),
                SpecialItems::faIconFromType(Enforce::string($rawSpecialItem['type'] ?? '')),
                Enforce::int($rawSpecialItem['count'] ?? 0),
            );
        }

        $items = $this->rawToItems(Enforce::array($rawFilterData['items'] ?? []));

        $filterData = new FilterData($items, $specialItems);

        return $filterData;
    }

    /**
     * @param array<mixed> $rawItems
     *
     * @return list<Item>
     */
    private function rawToItems(array $rawItems): array
    {
        $result = [];

        foreach ($rawItems as $rawItem) {
            $rawItem = Enforce::array($rawItem);

            $result[] = new Item(
                Enforce::string($rawItem['value'] ?? ''),
                Enforce::string($rawItem['label'] ?? ''),
                Enforce::int($rawItem['count'] ?? 0),
                $this->rawToItems(Enforce::array($rawItem['subItems'] ?? [])),
            );
        }

        return $result;
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

        foreach ($this->offerStatusRepository->getDistinctWithOpenCount() as $offer => $openCount) {
            $result->items->addComplexItem($offer, $offer, (int) $openCount);
        }

        return FilterData::from($result);
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

        return FilterData::from($result);
    }

    /**
     * @throws UnexpectedResultException
     */
    private function getInactiveFilterData(): FilterData
    {
        $inactiveCount = $this->artisanRepository->countAll() - $this->artisanRepository->countActive();

        return FilterData::from(new MutableFilterData(SpecialItems::newInactive($inactiveCount)));
    }
}
