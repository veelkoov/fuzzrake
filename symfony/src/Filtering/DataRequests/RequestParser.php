<?php

declare(strict_types=1);

namespace App\Filtering\DataRequests;

use InvalidArgumentException;
use Psl\Dict;
use Psl\Type;
use Psl\Type\Exception\CoercionException;
use Symfony\Component\HttpFoundation\Request;
use Veelkoov\Debris\Maps\StringToBool;
use Veelkoov\Debris\Maps\StringToString;
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
            new StringSet($strArrays['countries']),
            new StringSet($strArrays['states']),
            new StringSet($strArrays['languages']),
            new StringSet($strArrays['styles']),
            new StringSet($strArrays['features']),
            new StringSet($strArrays['orderTypes']),
            new StringSet($strArrays['productionModels']),
            new StringSet($strArrays['openFor']),
            new StringSet($strArrays['species']),
            arr_contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_UNKNOWN),
            arr_contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_SUPPORTED),
            arr_contains($strArrays['paymentPlans'], Consts::FILTER_VALUE_PAYPLANS_NONE),
            $booleans->get('isAdult'),
            $booleans->get('wantsSfw'),
            arr_contains($strArrays['inactive'], Consts::FILTER_VALUE_INCLUDE_INACTIVE),
            $booleans->get('creatorMode'),
            $pageNumber,
        ));
    }

    /**
     * @return array<string, list<string>>
     */
    private static function getStrArraysFromRequest(Request $request): array
    {
        /* @phpstan-ignore method.internal (Unsure how to fix currently) */
        $result = Dict\from_keys(self::ARRAYS, static fn ($reqKey) => $request->get($reqKey, []));
        $dataShape = Type\shape(Dict\from_keys(self::ARRAYS, static fn ($_) => Type\vec(Type\string())));

        try {
            return $dataShape->coerce($result);
        } catch (CoercionException $exception) {
            throw new InvalidArgumentException(previous: $exception);
        }
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
