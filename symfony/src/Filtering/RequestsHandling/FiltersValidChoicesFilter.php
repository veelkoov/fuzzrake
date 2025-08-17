<?php

declare(strict_types=1);

namespace App\Filtering\RequestsHandling;

use App\Data\Definitions\Features;
use App\Data\Definitions\OrderTypes;
use App\Data\Definitions\ProductionModels;
use App\Data\Definitions\Styles;
use App\Filtering\Consts;
use App\Service\DataService;
use App\Species\SpeciesService;
use Veelkoov\Debris\StringSet;

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
            new StringSet(Styles::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $features = self::onlyValidValues($choices->features,
            new StringSet(Features::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $orderTypes = self::onlyValidValues($choices->orderTypes,
            new StringSet(OrderTypes::getValues()), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $productionModels = self::onlyValidValues($choices->productionModels,
            new StringSet(ProductionModels::getValues()), Consts::FILTER_VALUE_UNKNOWN);

        $species = self::onlyValidValues($choices->species,
            $this->speciesService->getValidNames(), Consts::FILTER_VALUE_UNKNOWN);

        $openFor = self::onlyValidValues($choices->openFor,
            $this->dataService->getOpenFor(), Consts::FILTER_VALUE_NOT_TRACKED, Consts::FILTER_VALUE_TRACKING_ISSUES);

        $paymentPlans = self::onlyValidValues($choices->paymentPlans,
            StringSet::of(Consts::FILTER_VALUE_PAYPLANS_SUPPORTED, Consts::FILTER_VALUE_PAYPLANS_NONE, Consts::FILTER_VALUE_UNKNOWN));

        return new Choices(
            $choices->creatorId,
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
            $paymentPlans,
            $choices->isAdult,
            $choices->wantsSfw,
            $choices->wantsInactive,
            $choices->creatorMode,
            $choices->pageNumber,
        );
    }

    private static function onlyValidValues(StringSet $givenOptions, StringSet $validOptions, string ...$additionalValidOptions): StringSet
    {
        return $givenOptions->intersect($validOptions->plusAll($additionalValidOptions));
    }
}
