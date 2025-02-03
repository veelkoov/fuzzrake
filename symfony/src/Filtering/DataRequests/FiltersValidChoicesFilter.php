<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Data\Definitions\Features;
use App\Data\Definitions\OrderTypes;
use App\Data\Definitions\ProductionModels;
use App\Data\Definitions\Styles;
use App\Service\DataService;
use App\Service\SpeciesService;
use App\Utils\Collections\StringList;

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
            $this->dataService->getCountries(), Consts::FILTER_VALUE_UNKNOWN);
        $states = self::onlyValidValues($choices->states,
            $this->dataService->getStates(), Consts::FILTER_VALUE_UNKNOWN);
        $languages = self::onlyValidValues($choices->languages,
            $this->dataService->getLanguages(), Consts::FILTER_VALUE_UNKNOWN);

        $styles = self::onlyValidValues($choices->styles,
            new StringList(Styles::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $features = self::onlyValidValues($choices->features,
            new StringList(Features::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $orderTypes = self::onlyValidValues($choices->orderTypes,
            new StringList(OrderTypes::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $productionModels = self::onlyValidValues($choices->productionModels,
            new StringList(ProductionModels::getValues()), Consts::FILTER_VALUE_UNKNOWN);

        $species = self::onlyValidValues($choices->species,
            $this->speciesService->validNames, Consts::FILTER_VALUE_UNKNOWN);

        $openFor = self::onlyValidValues($choices->openFor,
            $this->dataService->getOpenFor(), Consts::FILTER_VALUE_NOT_TRACKED, Consts::FILTER_VALUE_TRACKING_ISSUES);

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
        return $givenOptions->intersect($validOptions->plusAll($additionalValidOptions));
    }
}
