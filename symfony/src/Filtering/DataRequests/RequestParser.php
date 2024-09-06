<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

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
        'openFor',
        'species',
        'paymentPlans',
        'inactive',
    ];

    private const BOOLEANS = [
        'isAdult',
        'wantsSfw',
        'creatorMode',
    ];

    private const STRINGS = [
        'textSearch',
        'makerId',
    ];

    public function __construct(
        private readonly FiltersValidChoicesFilter $filter,
    ) {
    }

    public function getChoices(Request $request): Choices
    {
        $dataShape = Type\shape(Dict\from_keys(self::ARRAYS, fn ($_) => Type\vec(Type\string())));
        $strArrays = $dataShape->coerce(self::getStrArraysFromRequest($request));

        $dataShape = Type\shape(Dict\from_keys(self::BOOLEANS, fn ($_) => Type\bool()));
        $booleans = $dataShape->coerce(self::getBooleansFromRequest($request));

        $dataShape = Type\shape(Dict\from_keys(self::STRINGS, fn ($_) => Type\string()));
        $strings = $dataShape->coerce(self::getStringsFromRequest($request));

        return $this->filter->getOnlyValidChoices(new Choices(
            $strings['makerId'],
            $strings['textSearch'],
            $strArrays['countries'],
            $strArrays['states'],
            $strArrays['languages'],
            $strArrays['styles'],
            $strArrays['features'],
            $strArrays['orderTypes'],
            $strArrays['productionModels'],
            $strArrays['openFor'],
            $strArrays['species'],
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_UNKNOWN),
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_SUPPORTED),
            contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_NONE),
            $booleans['isAdult'],
            $booleans['wantsSfw'],
            contains($strArrays['inactive'], Consts::FILTER_VALUE_INCLUDE_INACTIVE),
            $booleans['creatorMode'],
        ));
    }

    private static function getStrArraysFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::ARRAYS, fn ($reqKey) => $request->get($reqKey, []));
    }

    private static function getBooleansFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::BOOLEANS, fn ($reqKey) => $request->get($reqKey, false));
    }

    private static function getStringsFromRequest(Request $request): mixed
    {
        return Dict\from_keys(self::STRINGS, fn ($reqKey) => $request->get($reqKey, ''));
    }
}
