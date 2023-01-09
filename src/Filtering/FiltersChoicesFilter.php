<?php

declare(strict_types=1);

namespace App\Filtering;

use App\DataDefinitions\Features;
use App\DataDefinitions\OrderTypes;
use App\DataDefinitions\ProductionModels;
use App\DataDefinitions\Styles;
use App\Service\DataService;
use App\Utils\Species\SpeciesService;

class FiltersChoicesFilter
{
    public function __construct(
        private readonly DataService $dataService,
        private readonly SpeciesService $speciesService,
    ) {
    }

    public function getOnlyAllowed(Choices $choices): Choices
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

        $openFor = self::onlyValidValues($choices->commissionStatuses,
            $this->dataService->getOpenFor(), Consts::FILTER_VALUE_NOT_TRACKED, Consts::FILTER_VALUE_TRACKING_ISSUES);

        return new Choices(
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
