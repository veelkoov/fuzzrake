<?php

declare(strict_types=1);

namespace App\Filtering\RequestsHandling;

use App\Filtering\Consts;
use Symfony\Component\HttpFoundation\Request;
use Veelkoov\Debris\Maps\StringToBool;
use Veelkoov\Debris\Maps\StringToString;
use Veelkoov\Debris\Maps\StringToStringList;
use Veelkoov\Debris\StringList;
use Veelkoov\Debris\StringSet;

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
        'creatorId',
    ];

    public function __construct(
        private readonly FiltersValidChoicesFilter $filter,
    ) {
    }

    public function getChoices(Request $request): Choices
    {
        $strArrays = self::getStrArraysFromRequest($request);
        $booleans = self::getBooleansFromRequest($request);
        $strings = self::getStringsFromRequest($request);

        $pageNumber = $request->query->getInt('page', 1);

        return $this->filter->getOnlyValidChoices(new Choices(
            $strings->get('creatorId'),
            $strings->get('textSearch'),
            new StringSet($strArrays->get('countries')),
            new StringSet($strArrays->get('states')),
            new StringSet($strArrays->get('languages')),
            new StringSet($strArrays->get('styles')),
            new StringSet($strArrays->get('features')),
            new StringSet($strArrays->get('orderTypes')),
            new StringSet($strArrays->get('productionModels')),
            new StringSet($strArrays->get('openFor')),
            new StringSet($strArrays->get('species')),
            new StringSet($strArrays->get('paymentPlans')),
            $booleans->get('isAdult'),
            $booleans->get('wantsSfw'),
            $strArrays->get('inactive')->contains(Consts::FILTER_VALUE_INCLUDE_INACTIVE),
            $booleans->get('creatorMode'),
            $pageNumber,
        ));
    }

    private static function getStrArraysFromRequest(Request $request): StringToStringList
    {
        return StringToStringList::fromKeys(
            self::ARRAYS,
            static fn (string $paramName) => StringList::fromUnsafe($request->query->all($paramName)),
        );
    }

    private static function getBooleansFromRequest(Request $request): StringToBool
    {
        return StringToBool::fromKeys(self::BOOLEANS, static fn ($reqKey) => $request->query->getBoolean($reqKey, false));
    }

    private static function getStringsFromRequest(Request $request): StringToString
    {
        return StringToString::fromKeys(self::STRINGS, static fn ($reqKey) => $request->query->get($reqKey, ''));
    }
}
