<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use Psl\Dict;
use Psl\Iter;
use Psl\Type;
use Symfony\Component\HttpFoundation\Request;
use Veelkoov\Debris\StringList;

class RequestParser
{
    private const array ARRAYS = [
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

    private const array BOOLEANS = [
        'isAdult',
        'wantsSfw',
        'creatorMode',
    ];

    private const array STRINGS = [
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

        $dataShape = Type\positive_int();
        $pageNumber = $dataShape->coerce($request->get('page', 1));

        return $this->filter->getOnlyValidChoices(new Choices(
            $strings['makerId'],
            $strings['textSearch'],
            StringList::from($strArrays['countries']),
            StringList::from($strArrays['states']),
            StringList::from($strArrays['languages']),
            StringList::from($strArrays['styles']),
            StringList::from($strArrays['features']),
            StringList::from($strArrays['orderTypes']),
            StringList::from($strArrays['productionModels']),
            StringList::from($strArrays['openFor']),
            StringList::from($strArrays['species']),
            Iter\contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_UNKNOWN),
            Iter\contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_SUPPORTED),
            Iter\contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_NONE),
            $booleans['isAdult'],
            $booleans['wantsSfw'],
            Iter\contains($strArrays['inactive'], Consts::FILTER_VALUE_INCLUDE_INACTIVE),
            $booleans['creatorMode'],
            $pageNumber,
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
