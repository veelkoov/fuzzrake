<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Data\Definitions\Fields\Field;
use App\Filtering\DataRequests\Consts;
use App\Filtering\FiltersData\Builder\MutableFilterData;
use App\Filtering\FiltersData\Builder\SpecialItems;
use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Data\SpecialItemList;
use App\Repository\CreatorOfferStatusRepository;
use App\Repository\CreatorRepository;
use App\Repository\CreatorVolatileDataRepository;
use App\Service\Cache;
use App\Service\CountriesDataService;
use App\Service\DataService;
use App\Species\SpeciesFilterService;
use App\ValueObject\CacheTags;
use Doctrine\ORM\UnexpectedResultException;

class FiltersService
{
    public function __construct(
        private readonly CreatorRepository $creatorRepository,
        private readonly CreatorOfferStatusRepository $offerStatusRepository,
        private readonly CreatorVolatileDataRepository $creatorVolatileDataRepository,
        private readonly CountriesDataService $countriesDataService,
        private readonly DataService $dataService,
        private readonly SpeciesFilterService $speciesFilterService,
        private readonly Cache $cache,
    ) {
    }

    /**
     * @throws UnexpectedResultException
     */
    public function getCachedFiltersTplData(): FiltersData
    {
        return $this->cache->get(fn () => new FiltersData(
            $this->getValuesFilterData(Field::ORDER_TYPES, Field::OTHER_ORDER_TYPES),
            $this->getValuesFilterData(Field::STYLES, Field::OTHER_STYLES),
            $this->getPaymentPlans(),
            $this->getValuesFilterData(Field::FEATURES, Field::OTHER_FEATURES),
            $this->getValuesFilterData(Field::PRODUCTION_MODELS),
            $this->getOpenFor(),
            $this->getValuesFilterData(Field::LANGUAGES),
            $this->getCountriesFilterData(),
            $this->getStatesFilterData(),
            $this->speciesFilterService->getFilterData(),
            $this->getInactiveFilterData(),
        ), CacheTags::CREATORS, __METHOD__);
    }

    public function getCountriesFilterData(): FilterData
    {
        $unknown = SpecialItems::newUnknown();
        $result = new MutableFilterData($unknown);

        foreach ($this->countriesDataService->getRegions() as $regionName) {
            $result->items->addComplexItem($regionName, $regionName, 0);
        }

        $countriesData = $this->dataService->countDistinctInActiveCreatorsHaving(Field::COUNTRY);

        foreach ($countriesData as $countryCode => $count) {
            if ('' === $countryCode) {
                $unknown->incCount($count);
                continue;
            }

            $region = $this->countriesDataService->getRegionFrom($countryCode);
            $name = $this->countriesDataService->getNameFor($countryCode);

            $result->items[$region]->incCount($count);
            $result->items[$region]->subitems->addComplexItem($countryCode, $name, $count);
        }

        foreach ($result->items as $item) {
            $item->subitems->sort();
        }

        return FilterData::from($result);
    }

    public function getStatesFilterData(): FilterData
    {
        $unknown = SpecialItems::newUnknown();
        $result = new MutableFilterData($unknown);

        $statesData = $this->dataService->countDistinctInActiveCreatorsHaving(Field::STATE);

        foreach ($statesData as $state => $count) {
            if ('' === $state) {
                $unknown->incCount($count);
                continue;
            }

            $result->items->addOrIncItem($state, $count);
        }

        return FilterData::from($result);
    }

    /**
     * @throws UnexpectedResultException
     */
    private function getOpenFor(): FilterData
    {
        $trackedCount = $this->creatorRepository->getCsTrackedCount();
        $issuesCount = $this->creatorVolatileDataRepository->getCsTrackingIssuesCount();
        $activeCount = $this->dataService->countActiveCreators();
        $nonTrackedCount = $activeCount - $trackedCount;

        $trackingIssues = SpecialItems::newTrackingIssues($issuesCount);
        $notTracked = SpecialItems::newNotTracked($nonTrackedCount);
        $result = new MutableFilterData($trackingIssues, $notTracked);

        foreach ($this->offerStatusRepository->getDistinctWithOpenCount() as $offer => $openCount) {
            $result->items->addComplexItem($offer, $offer, $openCount);
        }

        return FilterData::from($result);
    }

    private function getPaymentPlans(): FilterData
    {
        $unknown = SpecialItems::newUnknown();
        $result = new MutableFilterData($unknown);

        foreach ($this->creatorRepository->getPaymentPlans() as $paymentPlan) {
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
        $inactiveCount = $this->creatorRepository->countAll() - $this->dataService->countActiveCreators();

        return FilterData::from(new MutableFilterData(SpecialItems::newInactive($inactiveCount)));
    }

    public function getValuesFilterData(Field $primaryField, ?Field $otherField = null): FilterData
    {
        $fields = arr_filter_nulls([$primaryField, $otherField]);

        $unknownCount = $this->dataService->countActiveCreators()
            - $this->dataService->countActiveCreatorsHavingAnyOf(...$fields);
        $specialItems = [SpecialItems::newUnknown($unknownCount)];

        if (null !== $otherField) {
            $specialItems[] = SpecialItems::newOther($this->dataService->countActiveCreatorsHavingAnyOf($otherField));
        }

        $specialItems = SpecialItemList::mapFrom($specialItems, SpecialItem::from(...));

        $items = ItemList::mapFrom(
            $this->dataService->countDistinctInActiveCreatorsHaving($primaryField),
            fn (int $count, string $item): Item => new Item($item, $item, $count),
        );

        return new FilterData($items, $specialItems);
    }
}
