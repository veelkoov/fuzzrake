<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use App\Data\Definitions\Features;
use App\Data\Definitions\OrderTypes;
use App\Data\Definitions\ProductionModels;
use App\Data\Definitions\Styles;
use App\Data\Species\SpeciesService;
use App\Service\DataService;

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
            Styles::getValues(), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $features = self::onlyValidValues($choices->features,
            Features::getValues(), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $orderTypes = self::onlyValidValues($choices->orderTypes,
            OrderTypes::getValues(), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $productionModels = self::onlyValidValues($choices->productionModels,
            ProductionModels::getValues(), ProductionModels::getValues(), Consts::FILTER_VALUE_UNKNOWN);

        $species = self::onlyValidValues($choices->species,
            $this->speciesService->getValidNames(), Consts::FILTER_VALUE_UNKNOWN);

        $openFor = self::onlyValidValues($choices->openFor,
            $this->dataService->getOpenFor(), Consts::FILTER_VALUE_NOT_TRACKED, Consts::FILTER_VALUE_TRACKING_ISSUES);

        return new Choices(
            $choices->makerId,
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
        );
    }

    /**
     * @param list<string>        $givenOptions
     * @param string|list<string> ...$validOptions
     *
     * @return list<string>
     */
    private static function onlyValidValues(array $givenOptions, string|array ...$validOptions): array
    {
        $allowed = [];

        foreach ($validOptions as $options) {
            if (is_string($options)) {
                $options = [$options];
            }

            foreach ($options as $option) {
                $allowed[] = $option;
            }
        }

        return array_intersect($givenOptions, $allowed);
    }
}
