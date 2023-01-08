<?php

declare(strict_types=1);

namespace App\Filtering;

use App\DataDefinitions\Features;
use App\DataDefinitions\OrderTypes;
use App\DataDefinitions\ProductionModels;
use App\DataDefinitions\Styles;
use App\Service\DataService;
use App\Utils\Species\SpeciesService;
use Psl\Dict;
use Psl\Type;
use Symfony\Component\HttpFoundation\Request;

use function Psl\Iter\contains;

class RequestParser
{
    private const ARRAYS = [
        'countries',
        'states',
        'languages',
        'styles',
        'features',
        'orderTypes',
        'productionModels',
        'commissionStatuses',
        'species',
        'paymentPlans',
    ];

    private const BOOLEANS = [
        'isAdult',
        'wantsSfw',
    ];

    public function __construct(
        private readonly DataService $dataService,
        private readonly SpeciesService $speciesService,
    ) {
    }

    public function getChoices(Request $request): Choices
    {
        $dataShape = Type\shape(Dict\from_keys(self::ARRAYS, fn ($_) => Type\vec(Type\string())));
        $strArrays = $dataShape->coerce(self::getStrArraysFromRequest($request));

        $dataShape = Type\shape(Dict\from_keys(self::BOOLEANS, fn ($_) => Type\bool()));
        $booleans = $dataShape->coerce(self::getBooleansFromRequest($request));

        // TODO: test validation

        $countries = self::onlyValidValues($strArrays['countries'], $this->dataService->getCountries(), Consts::FILTER_VALUE_UNKNOWN);
        $states = self::onlyValidValues($strArrays['states'], $this->dataService->getStates(), Consts::FILTER_VALUE_UNKNOWN);
        $languages = self::onlyValidValues($strArrays['languages'], $this->dataService->getLanguages());

        $styles = self::onlyValidValues($strArrays['styles'],
            Styles::getValues(), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $features = self::onlyValidValues($strArrays['features'],
            Features::getValues(), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $orderTypes = self::onlyValidValues($strArrays['orderTypes'],
            OrderTypes::getValues(), Consts::FILTER_VALUE_UNKNOWN, Consts::FILTER_VALUE_OTHER);
        $productionModels = self::onlyValidValues($strArrays['productionModels'],
            ProductionModels::getValues(), ProductionModels::getValues(), Consts::FILTER_VALUE_UNKNOWN);

        $species = self::onlyValidValues($strArrays['species'],
            $this->speciesService->getValidNames(), Consts::FILTER_VALUE_UNKNOWN);

        $openFor = self::onlyValidValues($strArrays['commissionStatuses'], $this->dataService->getOpenFor(), Consts::FILTER_VALUE_NOT_TRACKED, Consts::FILTER_VALUE_TRACKING_ISSUES);

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
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_UNKNOWN),
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_SUPPORTED),
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_NONE),
            $booleans['isAdult'],
            $booleans['wantsSfw'],
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

    private static function getStrArraysFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::ARRAYS, fn ($reqKey) => $request->get($reqKey, []));
    }

    private static function getBooleansFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::BOOLEANS, fn ($reqKey) => $request->get($reqKey, false));
    }
}
