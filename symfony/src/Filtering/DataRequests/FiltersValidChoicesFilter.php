<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Data\Definitions\Features;
use App\Data\Definitions\OrderTypes;
use App\Data\Definitions\ProductionModels;
use App\Data\Definitions\Styles;
use App\Service\DataService;
use App\Service\SpeciesService;
use Veelkoov\Debris\StringList;

class FiltersValidChoicesFilter
{
    public function __construct(
        private readonly DataService $dataService,
        private readonly SpeciesService $speciesService,
    ) {
    }

    public function getOnlyValidChoices(Choices $choices): Choices
    {
        $countries = self::onlyValidValues($choices->countries,
            StringList::from($this->dataService->getCountries()), Consts::FILTER_VALUE_UNKNOWN);
        $states = self::onlyValidValues($choices->states,
            StringList::from($this->dataService->getStates()), Consts::FILTER_VALUE_UNKNOWN);
        $languages = self::onlyValidValues($choices->languages,
            StringList::from($this->dataService->getLanguages()), Consts::FILTER_VALUE_UNKNOWN);

        $styles = self::onlyValidValues($choices->styles,
            StringList::from(Styles::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $features = self::onlyValidValues($choices->features,
            StringList::from(Features::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $orderTypes = self::onlyValidValues($choices->orderTypes,
            StringList::from(OrderTypes::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $productionModels = self::onlyValidValues($choices->productionModels,
            StringList::from(ProductionModels::getValues()), Consts::FILTER_VALUE_UNKNOWN);

        $species = self::onlyValidValues($choices->species,
            StringList::from($this->speciesService->getValidNames()), Consts::FILTER_VALUE_UNKNOWN);

        $openFor = self::onlyValidValues($choices->openFor,
            StringList::from($this->dataService->getOpenFor()), Consts::FILTER_VALUE_NOT_TRACKED, Consts::FILTER_VALUE_TRACKING_ISSUES);

        return new Choices(
            $choices->makerId,
            $choices->textSearch,
            $countries,
            $states,
            $languages,
            $styles,
            $features,
            $orderTypes,
            $productionModels,
            $openFor,
            $species,
            $choices->wantsUnknownPaymentPlans,
            $choices->wantsAnyPaymentPlans,
            $choices->wantsNoPaymentPlans,
            $choices->isAdult,
            $choices->wantsSfw,
            $choices->wantsInactive,
            $choices->creatorMode,
            $choices->pageNumber,
        );
    }

    private static function onlyValidValues(StringList $givenOptions, StringList $validOptions, string ...$additionalValidOptions): StringList
    {
        return $validOptions->plusAll($additionalValidOptions)->intersect($givenOptions);
    }
}
